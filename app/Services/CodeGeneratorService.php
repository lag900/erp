<?php

namespace App\Services;

use App\Models\Building;

class CodeGeneratorService
{
    public static function generateBuildingCode(string $englishName, int $locationId): string
    {
        // Remove non-English characters for safe processing
        $clean = preg_replace('/[^A-Za-z0-9 ]/', '', $englishName);

        // Fallback if no valid English characters
        if (empty(trim($clean))) {
            return 'BLD-' . time();
        }

        $words = array_filter(explode(' ', trim($clean)));
        $code = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Fallback if no valid code generated
        if (empty($code)) {
            $code = 'BLD';
        }
        
        $baseCode = $code;
        
        // Check for duplicates within same location
        $count = Building::where('location_id', $locationId)
            ->where('code', 'LIKE', $baseCode . '%')
            ->count();
        
        return $count ? $baseCode . '-' . ($count + 1) : $baseCode;
    }

    public static function generateModelCode(string $name, string $modelClass): string
    {
        // Keep only alphanumeric and convert to uppercase
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $name);
        $base = strtoupper(substr($cleanName, 0, 3));
        
        if (empty($base)) {
            $base = 'CAT'; // Default fallback
        }

        // Count existing records starting with this base
        $count = $modelClass::where('code', 'LIKE', $base . '%')->count();
        
        if ($count === 0) {
            return $base;
        }

        // COM (count 1) -> COM1
        // COM, COM1 (count 2) -> COM2
        return $base . $count;
    }
}
