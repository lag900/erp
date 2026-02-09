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
        'ig_page_url',
        'ig_enabled',
        'ig_embed_token',
        'li_page_url',
        'li_enabled',
    ];

    protected $casts = [
        'fb_enabled' => 'boolean',
        'fb_auto_publish' => 'boolean',
        'ig_enabled' => 'boolean',
        'li_enabled' => 'boolean',
    ];
}
