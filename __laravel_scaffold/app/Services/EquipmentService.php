<?php

namespace App\Services;

use App\Enums\EquipmentStatus;
use App\Enums\EquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Member;
use App\Models\Spot;
use App\Models\Zone;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EquipmentService
{
    private const REQUIRED_COLUMNS = ['name', 'asset_tag', 'type', 'category'];

    public function __construct(private readonly ActivityLogService $activityLogs) {}

    /** Returns a safe, non-persistent import preview. */
    public function previewCsv(UploadedFile $file): array
    {
        [$headers, $rawRows] = $this->readCsv($file);
        $missing = array_values(array_diff(self::REQUIRED_COLUMNS, $headers));
        if ($missing !== []) {
            return ['valid' => false, 'rows' => [], 'errors' => ['Missing required CSV columns: '.implode(', ', $missing).'.']];
        }

        $seenTags = [];
        $rows = [];
        foreach ($rawRows as $line => $raw) {
            $data = $this->normalizeRow(array_combine($headers, $raw));
            $errors = $this->rowErrors($data);
            $tag = $data['asset_tag'] ?? null;
            if ($tag !== null && isset($seenTags[$tag])) {
                $errors[] = "Duplicate asset_tag '{$tag}' also appears on line {$seenTags[$tag]}.";
            }
            $seenTags[$tag] = $line;
            $rows[] = [
                'line' => $line,
                'action' => $errors !== [] ? 'error' : (Equipment::withTrashed()->where('asset_tag', $tag)->exists() ? 'update' : 'create'),
                'asset_tag' => $tag,
                'data' => $data,
                'errors' => $errors,
            ];
        }

        return [
            'valid' => collect($rows)->every(fn (array $row) => $row['errors'] === []),
            'rows' => $rows,
            'errors' => collect($rows)->flatMap(fn (array $row) => array_map(fn (string $error) => "Line {$row['line']}: {$error}", $row['errors']))->values()->all(),
        ];
    }

    /** Valid rows are committed as one transaction; invalid imports do not alter data. */
    public function importCsv(UploadedFile $file, ?Member $actor = null): array
    {
        $preview = $this->previewCsv($file);
        if (! $preview['valid']) {
            throw ValidationException::withMessages(['file' => $preview['errors']]);
        }

        return DB::transaction(function () use ($preview, $actor) {
            $created = 0;
            $updated = 0;
            foreach ($preview['rows'] as $row) {
                $data = $row['data'];
                $category = EquipmentCategory::firstOrCreate(['name' => $data['category']], ['description' => $data['category_description'] ?? null]);
                $attributes = [
                    'name' => $data['name'], 'category_id' => $category->id, 'subcategory' => $data['subcategory'] ?? 'General',
                    'type' => $data['type'], 'status' => $data['status'] ?? EquipmentStatus::AVAILABLE->value,
                    'zone_id' => $data['zone_id'] ?? null, 'spot_id' => $data['spot_id'] ?? null,
                    'quantity_total' => $data['quantity_total'] ?? 1, 'quantity_available' => $data['quantity_available'] ?? ($data['quantity_total'] ?? 1),
                    'min_stock_threshold' => $data['min_stock_threshold'] ?? 0, 'allow_borrow' => $data['allow_borrow'] ?? false,
                    'max_borrow_days' => $data['max_borrow_days'] ?? 0, 'photo_url' => $data['photo_url'] ?? null,
                    'manual_url' => $data['manual_url'] ?? null, 'notes' => $data['notes'] ?? null,
                ];
                $equipment = Equipment::withTrashed()->firstOrNew(['asset_tag' => $data['asset_tag']]);
                $wasExisting = $equipment->exists;
                $oldValues = $wasExisting ? $equipment->only(array_keys($attributes)) : null;
                $equipment->fill($attributes);
                if ($equipment->trashed()) {
                    $equipment->restore();
                }
                $equipment->save();
                $this->activityLogs->record($equipment, $wasExisting ? 'equipment.imported_updated' : 'equipment.imported_created', $actor, $oldValues, $equipment->only(array_keys($attributes)));
                $wasExisting ? $updated++ : $created++;
            }

            return ['success' => true, 'created_count' => $created, 'updated_count' => $updated, 'imported_count' => $created + $updated, 'rows' => $preview['rows']];
        });
    }

    public function parseCsvAndImport(UploadedFile $file, ?Member $actor = null): array
    {
        return $this->importCsv($file, $actor);
    }

    private function readCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headerRow = fgetcsv($handle);
        if ($headerRow === false) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'The CSV file is empty.']);
        }
        $headers = array_map(fn ($header) => $this->canonicalHeader((string) $header), $headerRow);
        if (count($headers) !== count(array_unique($headers))) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'CSV headers must be unique.']);
        }
        $rows = [];
        $line = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if ($row === [null] || $row === [] || ! array_filter($row, fn ($value) => trim((string) $value) !== '')) {
                continue;
            }
            if (count($row) !== count($headers)) {
                fclose($handle);
                throw ValidationException::withMessages(['file' => "Line {$line} has a different number of columns than the header."]);
            }
            $rows[$line] = array_map(fn ($value) => trim((string) $value), $row);
        }
        fclose($handle);

        return [$headers, $rows];
    }

    private function canonicalHeader(string $header): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', trim(ltrim($header, "\xEF\xBB\xBF"))));

        return match ($key) {
            'category_name' => 'category', 'assettag', 'asset_tag_id' => 'asset_tag', 'min_stock' => 'min_stock_threshold',
            'quantity' => 'quantity_total', default => $key,
        };
    }

    private function normalizeRow(array $row): array
    {
        foreach (['quantity_total', 'quantity_available', 'min_stock_threshold', 'max_borrow_days', 'zone_id', 'spot_id'] as $integer) {
            if (($row[$integer] ?? '') !== '') {
                if (filter_var($row[$integer], FILTER_VALIDATE_INT) === false || (int) $row[$integer] < 0) {
                    $row['__invalid_numeric'][] = $integer;
                } else {
                    $row[$integer] = (int) $row[$integer];
                }
            } else {
                unset($row[$integer]);
            }
        }
        if (array_key_exists('allow_borrow', $row) && $row['allow_borrow'] !== '') {
            $row['allow_borrow'] = filter_var($row['allow_borrow'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        } else {
            unset($row['allow_borrow']);
        }

        return $row;
    }

    private function rowErrors(array $row): array
    {
        $errors = [];
        foreach (self::REQUIRED_COLUMNS as $column) {
            if (($row[$column] ?? '') === '') {
                $errors[] = "{$column} is required.";
            }
        }
        if (($row['type'] ?? null) !== null && ! in_array($row['type'], array_column(EquipmentType::cases(), 'value'), true)) {
            $errors[] = 'type must be durable or consumable.';
        }
        if (($row['status'] ?? '') !== '' && ! in_array($row['status'], array_column(EquipmentStatus::cases(), 'value'), true)) {
            $errors[] = 'status is invalid.';
        }
        foreach (['quantity_total', 'quantity_available', 'min_stock_threshold', 'max_borrow_days'] as $integer) {
            if (isset($row[$integer]) && $row[$integer] < 0) {
                $errors[] = "{$integer} cannot be negative.";
            }
        }
        foreach ($row['__invalid_numeric'] ?? [] as $column) {
            $errors[] = "{$column} must be a non-negative integer.";
        }
        if (isset($row['quantity_total'], $row['quantity_available']) && $row['quantity_available'] > $row['quantity_total']) {
            $errors[] = 'quantity_available cannot exceed quantity_total.';
        }
        if (array_key_exists('allow_borrow', $row) && $row['allow_borrow'] === null) {
            $errors[] = 'allow_borrow must be a boolean.';
        }
        if (isset($row['zone_id']) && ! Zone::whereKey($row['zone_id'])->exists()) {
            $errors[] = 'zone_id does not reference an existing zone.';
        }
        if (isset($row['spot_id']) && ! Spot::whereKey($row['spot_id'])->exists()) {
            $errors[] = 'spot_id does not reference an existing spot.';
        }
        if (isset($row['zone_id'], $row['spot_id']) && ! Spot::whereKey($row['spot_id'])->where('zone_id', $row['zone_id'])->exists()) {
            $errors[] = 'spot_id does not belong to zone_id.';
        }

        return $errors;
    }
}
