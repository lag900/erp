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
        return CodeGeneratorService::generateModelCode($name, Category::class);
    }
}
