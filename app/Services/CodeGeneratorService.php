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
            $baseCode = 'BLD';
        } else {
            $words = array_filter(explode(' ', trim($clean)));
            $code = '';
            
            foreach ($words as $word) {
                if (!empty($word)) {
                    $code .= strtoupper(substr($word, 0, 1));
                }
            }
            $baseCode = empty($code) ? 'BLD' : $code;
        }

        $code = $baseCode;
        $counter = 1;

        // Ensure uniqueness within the location
        while (Building::where('location_id', $locationId)->where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter;
            $counter++;
        }

        return $code;
    }

    public static function generateModelCode(string $name, string $modelClass): string
    {
        // Keep only alphanumeric and convert to uppercase
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $name);
        $base = strtoupper(substr($cleanName, 0, 3));
        
        if (empty($base) || strlen($base) < 2) {
            $base = 'CAT'; // Default fallback
        }

        $code = $base;
        $counter = 1;

        // Iteratively check for uniqueness to avoid duplicate key errors
        while ($modelClass::where('code', $code)->exists()) {
            $code = $base . $counter;
            $counter++;
        }

        return $code;
    }
}
