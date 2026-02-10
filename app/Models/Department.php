<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'arabic_name',
        'code',
        'description',
        'university_logo',
        'department_logo',
        'director_name',
        'director_title',
        'director_image',
        'display_order',
        'status',
        'type',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['is_default', 'role_id'])
            ->withTimestamps();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->withPivot(['is_enabled'])
            ->withTimestamps();
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function getUniversityLogoUrlAttribute(): ?string
    {
        return $this->university_logo ? asset('storage/' . $this->university_logo) : null;
    }

    public function getDepartmentLogoUrlAttribute(): ?string
    {
        return $this->department_logo ? asset('storage/' . $this->department_logo) : null;
    }

    public function isAdmin(): bool
    {
        return $this->code === 'ADMIN';
    }
}
