<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $assets;

    public function __construct($assets)
    {
        $this->assets = $assets;
    }

    public function collection()
    {
        return $this->assets;
    }

    public function headings(): array
    {
        if (isset($this->assets[0]) && isset($this->assets[0]->total)) {
            return ['Category Name', 'Total Assets'];
        }

        return [
            'ID',
            'Name',
            'Code',
            'Serial Number',
            'Category',
            'Subcategory',
            'Building',
            'Room',
            'Status',
            'Created At',
            'Created By',
        ];
    }

    public function map($asset): array
    {
        if (isset($asset->total)) {
            return [
                $asset->category->name ?? 'Uncategorized',
                $asset->total,
            ];
        }

        return [
            $asset->id,
            $asset->name,
            $asset->code,
            $asset->serial_number,
            $asset->category->name ?? '-',
            $asset->subCategory->name ?? '-',
            $asset->building->name ?? '-',
            $asset->room->name ?? '-',
            $asset->status,
            $asset->created_at->format('Y-m-d H:i'),
            $asset->creator->name ?? '-',
        ];
    }
}
