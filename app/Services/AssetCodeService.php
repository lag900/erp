<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Building;
use App\Models\SubCategory;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class AssetCodeService
{
    /**
     * Generate complete enterprise asset code
     */
    public function generateAssetCode(Asset $asset): string
    {
        $room = $asset->room()->with('level.building')->first();
        $category = $asset->category;
        $subCategory = $asset->subCategory;
        
        $parts = [];
        
        // 1. University Code (ORG)
        $parts[] = config('app.university_code', 'BAU');
        
        // 2. Smart Building Code (SYS)
        $parts[] = $this->getSmartBuildingCode($room->level->building);
        
        // 3. Floor (2 digits) (BLD)
        $parts[] = $this->formatFloor($room->level);
        
        // 4. Room Number (ROOM)
        $parts[] = $room->code;
        
        // 5. Category Code (3 letters) (CATEGORY)
        $parts[] = $this->generateSmartCode($category->name ?? 'GEN', 3);
        
        // 6. Subcategory Code (3 letters) (SUB)
        $parts[] = $this->generateSmartCode($subCategory->name ?? 'GEN', 3);
        
        // --- Group & Bundle Logic ---
        $groupPrefix = $asset->category_prefix;
        $bundleNo = $asset->bundle_group_number;

        // Auto-inherit from parent if not set
        if ((!$groupPrefix || !$bundleNo) && $asset->parent_id) {
            $parent = Asset::find($asset->parent_id);
            if ($parent) {
                $groupPrefix = $parent->category_prefix;
                $bundleNo = $parent->bundle_group_number;
            }
        }

        // Fallback to category defaults if still not set
        $groupPrefix = $groupPrefix ?: ($category->prefix ?? 'S00');
        $bundleNo = $bundleNo ?: ($asset->series_no ?? 1);

        // 7. Group Prefix (S01, etc.)
        $parts[] = $groupPrefix;
        
        // 8. Bundle Number (BUNDLENO)
        $parts[] = (string) $bundleNo;
        
        // 9. Global Counter (5 digits) (UNIQUEID)
        $counter = $asset->id ?: $this->getNextGlobalCounter();
        $parts[] = str_pad($counter, 5, '0', STR_PAD_LEFT);
        
        return implode('-', $parts);
    }

    /**
     * Generate smart building code from building name
     * "Management System Lab" → MSL
     * "Main Engineering Building" → MEB
     */
    private function getSmartBuildingCode(Building $building): string
    {
        if ($building->code) {
            return strtoupper($building->code);
        }

        // Generate from name
        $words = preg_split('/\s+/', trim($building->name));
        $code = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper($word[0]);
            }
        }
        
        // Max 4 chars
        $code = substr($code, 0, 4);
        
        // Ensure uniqueness
        $code = $this->ensureUniqueBuildingCode($code, $building->id);
        
        // Save to building
        $building->update(['code' => $code]);
        
        return $code;
    }

    /**
     * Ensure building code is unique
     */
    private function ensureUniqueBuildingCode(string $baseCode, int $buildingId): string
    {
        $code = $baseCode;
        $counter = 2;
        
        while (Building::where('code', $code)->where('id', '!=', $buildingId)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Format floor number to 2 digits
     * 1 → 01, Ground → 00
     */
    private function formatFloor($level): string
    {
        if ($level->code) {
            return str_pad($level->code, 2, '0', STR_PAD_LEFT);
        }
        
        $name = strtolower($level->name);
        
        if (str_contains($name, 'ground') || str_contains($name, 'g')) {
            return '00';
        }
        
        // Extract number from name
        preg_match('/\d+/', $level->name, $matches);
        $number = $matches[0] ?? 1;
        
        return str_pad($number, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Generate smart code from name (first 3 letters)
     */
    private function generateSmartCode(string $name, int $length = 3): string
    {
        $clean = preg_replace('/[^A-Za-z]/', '', $name);
        return strtoupper(substr($clean, 0, $length));
    }

    /**
     * Get next global sequential counter
     */
    public function getNextGlobalCounter(): int
    {
        return DB::transaction(function () {
            $maxId = Asset::lockForUpdate()->max('id');
            return ($maxId ?: 0) + 1;
        });
    }

    /**
     * Legacy methods for backward compatibility
     */
    public function generateStandalone(Room $room, SubCategory $subCategory): array
    {
        $asset = new Asset([
            'room_id' => $room->id,
            'sub_category_id' => $subCategory->id,
            'series_no' => null,
            'component_no' => null,
        ]);
        
        $code = $this->generateAssetCode($asset);
        
        return [
            'code' => $code,
            'global_id' => $this->getNextGlobalCounter()
        ];
    }

    public function generateParent(Room $room, SubCategory $subCategory, int $seriesNo, int $componentNo = 1): array
    {
        $asset = new Asset([
            'room_id' => $room->id,
            'sub_category_id' => $subCategory->id,
            'series_no' => $seriesNo,
            'component_no' => $componentNo,
        ]);
        
        $code = $this->generateAssetCode($asset);
        
        return [
            'code' => $code,
            'global_id' => $this->getNextGlobalCounter(),
            'series_no' => $seriesNo,
            'component_no' => $componentNo
        ];
    }

    public function generateChild(Room $room, SubCategory $subCategory, int $seriesNo, int $componentNo): array
    {
        return $this->generateParent($room, $subCategory, $seriesNo, $componentNo);
    }

    public function getNextSeriesNoInt(Room $room): int
    {
        $maxSeries = Asset::where('room_id', $room->id)
            ->whereNotNull('series_no')
            ->max('series_no');
        
        return ($maxSeries ?: 0) + 1;
    }

    public function getNextComponentNo(int $parentId): int
    {
        $maxComp = Asset::where('parent_id', $parentId)->max('component_no');
        return ($maxComp ?: 0) + 1;
    }

    public function generate(Room $room, SubCategory $subCategory, ?int $seriesNo = null, ?int $componentNo = null): array
    {
        if ($seriesNo !== null) {
            return $this->generateChild($room, $subCategory, $seriesNo, $componentNo ?: 1);
        }
        return $this->generateStandalone($room, $subCategory);
    }

    /**
     * Get next bundle number for a specific category
     */
    public function getNextBundleNumber(int $categoryId): int
    {
        return DB::transaction(function () use ($categoryId) {
            $maxNumber = Asset::where('category_id', $categoryId)
                ->lockForUpdate()
                ->max('bundle_group_number');
            
            return ($maxNumber ?: 0) + 1;
        });
    }

    /**
     * Format the bundle serial based on category prefix and sequential number
     */
    public function generateBundleSerial(int $categoryId, int $bundleNumber): string
    {
        $category = \App\Models\Category::find($categoryId);
        $prefix = $category->prefix ?? 'S00';
        
        return $prefix . '-' . $bundleNumber;
    }
}
