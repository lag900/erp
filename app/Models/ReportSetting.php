<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'right_logo_path',
        'center_logo_path',
        'left_logo_path',
        'header_line_1',
        'header_line_2',
        'header_line_3',
        'header_text_color',
        'body_text_color',
        'custom_header_title',
        'signatures',
        'show_stamp',
        'stamp_label',
        'footer_text',
    ];

    protected $casts = [
        'signatures' => 'array',
        'show_stamp' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public static function forDepartment($departmentId)
    {
        return static::firstOrCreate(
            ['department_id' => $departmentId],
            [
                'signatures' => [],
                'header_text_color' => '#000000',
                'body_text_color' => '#1a1a1a',
                'show_stamp' => false,
            ]
        );
    }
}
