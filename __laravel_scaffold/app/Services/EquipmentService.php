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
        
        // قراءة وتنظيف أسماء الأعمدة (Headers) من أي مسافات مخفية
        $rawHeaders = fgetcsv($handle, 1000, ',');
        if (!$rawHeaders) {
            fclose($handle);
            throw new \Exception("ملف الـ CSV فارغ أو غير صالحة بنيته.");
        }
        $headers = array_map('trim', $rawHeaders);

        $importedCount = 0;
        $errors = [];
        $rowNumber = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;
                
                // تخطي الأسطر الفارغة
                if (empty($row) || $row === [null] || !array_filter($row)) {
                    continue;
                }

                // دمج الهيدرز مع قيم السطر
                if (count($headers) !== count($row)) {
                    $errors[] = "السطر {$rowNumber}: عدد الأعمدة لا يطابق الهيدر.";
                    continue;
                }

                $data = array_combine($headers, array_map('trim', $row));

                // 1. استخراج التصنيف
                $categoryName = $data['category_name'] ?? $data['Category'] ?? 'General';
                $category = EquipmentCategory::firstOrCreate([
                    'name' => $categoryName
                ]);

                // 2. استخراج الكود التعريفي asset_tag
                $assetTag = $data['asset_tag'] ?? $data['Asset Tag'] ?? $data['AssetTag'] ?? null;

                if (!$assetTag) {
                    $errors[] = "السطر {$rowNumber}: الكود التعريفي (asset_tag) مفقود.";
                    continue;
                }

                // الفحص عن التكرار
                if (Equipment::where('asset_tag', $assetTag)->exists()) {
                    $errors[] = "السطر {$rowNumber}: الكود التعريفي {$assetTag} مكرر بالفعل.";
                    continue;
                }

                // 3. قراءة باقي البيانات من $data (وليس $row!)
                $name        = $data['name'] ?? $data['Name'] ?? 'Unassigned';
                $subcategory = $data['subcategory'] ?? $data['Subcategory'] ?? 'General';
                $type        = $data['type'] ?? $data['Type'] ?? 'Hardware';
                $status      = $data['status'] ?? $data['Status'] ?? EquipmentStatus::AVAILABLE->value;
                $notes       = $data['notes'] ?? $data['Notes'] ?? null;

                // 4. الحفظ في قاعدة البيانات مع تعيين صريح للحقول المطلوبة
                $equipment = new Equipment();
                $equipment->name = $name;
                $equipment->category_id = $category->id;
                $equipment->subcategory = $subcategory;
                $equipment->type = $type;
                $equipment->asset_tag = $assetTag;
                $equipment->status = $status;
                $equipment->notes = $notes;
                $equipment->save();

                $importedCount++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw new \Exception("فشلت عملية الرفع: " . $e->getMessage());
        }

        fclose($handle);

        return [
            'success'        => true,
            'imported_count' => $importedCount,
            'errors'         => $errors
        ];
    }
}