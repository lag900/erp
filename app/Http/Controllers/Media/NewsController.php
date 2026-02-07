<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Jobs\PublishToFacebookJob;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::orderBy('publish_date', 'desc')->paginate(10);
        return Inertia::render('Media/News/Index', [
            'news' => $news
        ]);
    }

    public function create()
    {
        return Inertia::render('Media/News/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'category' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'status' => 'required|in:draft,published,archived',
            'publish_to_facebook' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('news', 'public');
            $validated['image'] = Storage::url($path);
        }

        $news = News::create($validated);

        if ($news->status === 'published' && $news->publish_to_facebook) {
            PublishToFacebookJob::dispatch($news);
        }

        return redirect()->route('media.news.index')->with('success', 'News created successfully.');
    }

    public function edit(News $news)
    {
        return Inertia::render('Media/News/Edit', [
            'news' => $news
        ]);
    }

    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'category' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'status' => 'required|in:draft,published,archived',
            'publish_to_facebook' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            // Delete old image
            if ($news->image) {
                $oldPath = str_replace('/storage/', '', $news->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image_file')->store('news', 'public');
            $validated['image'] = Storage::url($path);
        }

        $oldStatus = $news->status;
        $news->update($validated);

        // If status changed to published and FB is checked, or if already published and FB was just checked
        if ($news->status === 'published' && $news->publish_to_facebook && !$news->facebook_post_id && $news->facebook_publish_status !== 'pending') {
            PublishToFacebookJob::dispatch($news);
        }

        return redirect()->route('media.news.index')->with('success', 'News updated successfully.');
    }

    public function destroy(News $news)
    {
        if ($news->image) {
            $path = str_replace('/storage/', '', $news->image);
            Storage::disk('public')->delete($path);
        }
        $news->delete();
        return redirect()->route('media.news.index')->with('success', 'News deleted successfully.');
    }
}
