<?php

namespace App\Console\Commands;

use App\Services\ImageOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize 
                            {--folder= : Specific folder to optimize}
                            {--force : Force re-optimization of all images}';

    protected $description = 'Optimize all existing images in storage';

    protected $imageOptimizer;

    public function __construct(ImageOptimizationService $imageOptimizer)
    {
        parent::__construct();
        $this->imageOptimizer = $imageOptimizer;
    }

    public function handle()
    {
        $this->info('🖼️  Starting image optimization...');
        
        $folder = $this->option('folder') ?? '';
        $force = $this->option('force');
        
        // Get all image files from public storage
        $files = Storage::disk('public')->allFiles($folder);
        
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $imageFiles = collect($files)->filter(function ($file) use ($imageExtensions) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($extension, $imageExtensions);
        });
        
        $total = $imageFiles->count();
        $this->info("Found {$total} images to process");
        
        $bar = $this->output->createProgressBar($total);
        $bar->start();
        
        $optimized = 0;
        $skipped = 0;
        $failed = 0;
        
        foreach ($imageFiles as $file) {
            // Skip if already WebP and not forcing
            if (!$force && str_ends_with($file, '.webp')) {
                $skipped++;
                $bar->advance();
                continue;
            }
            
            try {
                $result = $this->imageOptimizer->optimizeExistingImage($file);
                if ($result) {
                    $optimized++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->error("\nFailed to optimize {$file}: " . $e->getMessage());
                $failed++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Optimization complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Optimized', $optimized],
                ['Skipped', $skipped],
                ['Failed', $failed],
                ['Total', $total],
            ]
        );
        
        return self::SUCCESS;
    }
}
