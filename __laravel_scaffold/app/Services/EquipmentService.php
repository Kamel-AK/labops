<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Enums\EquipmentStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class EquipmentService
{
    public function parseCsvAndImport(UploadedFile $file): array
{
    $handle = fopen($file->getRealPath(), 'r');
    
    $headers = fgetcsv($handle, 1000, ',');
    
    $importedCount = 0;
    $errors = [];
    $rowNumber = 1;

    DB::beginTransaction();
    try {
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $rowNumber++;
            
            if (empty($row) || $row === [null] || !array_filter($row)) {
                continue;
            }

            $data = array_combine($headers, $row);

            $category = EquipmentCategory::firstOrCreate([
                'name' => trim($data['category_name'])
            ]);

            if (Equipment::where('asset_tag', trim($data['asset_tag']))->exists()) {
                $errors[] = "السطر {$rowNumber}: الكود التعريفي {$data['asset_tag']} مكرر بالفعل.";
                continue;
            }

            Equipment::create([
                'equipment_category_id' => $category->id,
                'asset_tag' => trim($data['asset_tag']),
                'name' => trim($data['name']),
                'model_number' => trim($data['model_number']) ?: null,
                'status' => EquipmentStatus::AVAILABLE,
                'notes' => trim($data['notes']) ?: null,
            ]);

            $importedCount++;
        }

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        fclose($handle);
        throw new \Exception("فشلت عملية الرفع بسبب خطأ في بنية الملف: " . $e->getMessage());
    }

    fclose($handle);

    return [
        'success' => true,
        'imported_count' => $importedCount,
        'errors' => $errors
    ];
}
}