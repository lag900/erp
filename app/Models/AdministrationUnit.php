<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdministrationUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'director_name',
        'director_title',
        'director_image',
        'access_password',
        'display_order',
        'status',
    ];

    protected $casts = [
        'access_password' => 'hashed',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->director_image 
            ? asset('storage/' . $this->director_image) 
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->director_name) . '&color=F97316&background=FFF7ED';
    }
}
