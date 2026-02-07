<?php

namespace App\Jobs;

use App\Models\News;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishToFacebookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $news;

    /**
     * Create a new job instance.
     */
    public function __construct(News $news)
    {
        $this->news = $news;
    }

    /**
     * Execute the job.
     */
    public function handle(FacebookService $facebookService): void
    {
        Log::info("Starting Facebook publishing job for news: " . $this->news->id);

        $this->news->update(['facebook_publish_status' => 'pending']);

        $result = $facebookService->publishPost($this->news);

        if ($result['success']) {
            $this->news->update([
                'facebook_post_id' => $result['post_id'],
                'facebook_publish_status' => 'published',
                'facebook_error' => null
            ]);
            Log::info("Successfully published news {$this->news->id} to Facebook.");
        } else {
            $this->news->update([
                'facebook_publish_status' => 'failed',
                'facebook_error' => $result['error']
            ]);
            Log::error("Failed to publish news {$this->news->id} to Facebook: " . $result['error']);
        }
    }
}
