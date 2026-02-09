<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Get published news for the landing page (API).
     */
    public function index()
    {
        $news = News::where('status', 'published')
            ->orderBy('publish_date', 'desc')
            ->take(6)
            ->get();

        return response()->json($news);
    }

    /**
     * Display the news listing page.
     */
    public function listing()
    {
        $news = News::where('status', 'published')
            ->orderBy('publish_date', 'desc')
            ->paginate(12);

        $mediaSettings = \App\Models\MediaSetting::first();

        return \Inertia\Inertia::render('Public/News/Index', [
            'news' => $news,
            'mediaSettings' => $mediaSettings
        ]);
    }

    /**
     * Display a single news article page.
     */
    public function read($id)
    {
        $news = News::findOrFail($id);
        
        // Fetch related news (optional)
        $related = News::where('status', 'published')
            ->where('id', '!=', $id)
            ->orderBy('publish_date', 'desc')
            ->take(3)
            ->get();

        $mediaSettings = \App\Models\MediaSetting::first();

        return \Inertia\Inertia::render('Public/News/Show', [
            'article' => $news,
            'relatedNews' => $related,
            'mediaSettings' => $mediaSettings
        ]);
    }

    /**
     * Get a single news item (API).
     */
    public function show($id)
    {
        $news = News::findOrFail($id);
        return response()->json($news);
    }
}
