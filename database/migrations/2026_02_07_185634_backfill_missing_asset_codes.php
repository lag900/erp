<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Asset;
use App\Services\AssetCodeService;

return new class extends Migration
{
    public function up(): void
    {
        $codeService = app(AssetCodeService::class);
        
        Asset::whereNull('asset_code')
            ->orWhere('asset_code', '')
            ->with(['room.level.building', 'subCategory.category'])
            ->chunk(100, function ($assets) use ($codeService) {
                foreach ($assets as $asset) {
                    if (!$asset->room || !$asset->subCategory) {
                        continue;
                    }
                    
                    try {
                        $codeData = $codeService->generate(
                            $asset->room,
                            $asset->subCategory,
                            $asset->series_no,
                            $asset->component_no
                        );
                        
                        DB::table('assets')
                            ->where('id', $asset->id)
                            ->update([
                                'asset_code' => $codeData['code'],
                                'sequence_number' => $codeData['global_id'] ?? $asset->sequence_number
                            ]);
                        
                    } catch (\Exception $e) {
                        Log::error("Failed to generate code for asset {$asset->id}: " . $e->getMessage());
                    }
                }
            });
    }

    public function down(): void
    {
        // No rollback needed
    }
};
