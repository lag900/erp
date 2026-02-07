<?php

namespace App\Services;

use App\Models\MediaSetting;
use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected $settings;

    public function __construct()
    {
        $this->settings = MediaSetting::first();
    }

    /**
     * Publish a news item to a Facebook page.
     * 
     * @param News $news
     * @return array
     */
    public function publishPost(News $news)
    {
        if (!$this->settings || !$this->settings->fb_enabled || !$this->settings->fb_access_token || !$this->settings->fb_page_id) {
            return [
                'success' => false,
                'error' => 'Facebook integration is disabled or not configured.'
            ];
        }

        try {
            $pageId = $this->settings->fb_page_id;
            $accessToken = $this->settings->fb_access_token;
            
            // Prepare content
            $message = "{$news->title}\n\n" . strip_tags($news->description);
            $link = url("/news/{$news->id}"); // Replace with real URL if available

            $url = "https://graph.facebook.com/v19.0/{$pageId}/feed";

            $response = Http::post($url, [
                'message' => $message,
                'link' => $link,
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'post_id' => $data['id'] ?? null
                ];
            }

            return [
                'success' => false,
                'error' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error("Facebook publishing failed: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
