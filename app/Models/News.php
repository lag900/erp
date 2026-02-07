<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use \App\Traits\BelongsToDepartment;

    protected $fillable = [
        'department_id',
        'title',
        'description',
        'content',
        'image',
        'category',
        'publish_date',
        'status',
        'publish_to_facebook',
        'facebook_post_id',
        'facebook_publish_status',
        'facebook_error',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
        'publish_to_facebook' => 'boolean',
    ];
}
