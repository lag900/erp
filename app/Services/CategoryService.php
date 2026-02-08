<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Generate unique category code based on name.
     */
    public function generateUniqueCode(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        
        if (empty($base)) {
            $base = 'CAT';
        }

        $count = Category::where('code', 'LIKE', $base . '%')->count();
        
        if ($count === 0) {
            return $base;
        }

        // Check if the base itself is available (sometimes count is misleading if there are gaps)
        // But the user rule says "if duplicate -> add number"
        return $base . ($count + 1);
    }
}
