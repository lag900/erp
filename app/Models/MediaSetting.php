<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaSetting extends Model
{
    protected $fillable = [
        'fb_page_url',
        'fb_page_id',
        'fb_access_token',
        'fb_enabled',
        'fb_auto_publish',
    ];

    protected $casts = [
        'fb_enabled' => 'boolean',
        'fb_auto_publish' => 'boolean',
    ];
}
