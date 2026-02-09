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

    public function map($asset): array
    {
        return [
            $asset->id,
            $asset->description,
            $asset->category->name ?? '',
            $asset->subCategory->name ?? '',
            $asset->building->name ?? '',
            $asset->room->number ?? '',
            $asset->custodian_id ? 'Yes' : 'No',
            $asset->status,
            $asset->created_at->format('Y-m-d'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Description',
            'Category',
            'Sub Category',
            'Building',
            'Room',
            'Custodian',
            'Status',
            'Created At',
        ];
    }
}
