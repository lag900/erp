<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Get published news for the landing page.
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
     * Show a single news item.
     */
    public function show($id)
    {
        $news = News::findOrFail($id);
        return response()->json($news);
    }
}
