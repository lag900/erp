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
            'image_file' => 'nullable|image|max:5120',
            'category' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'status' => 'required|in:draft,published,archived',
            'publish_to_facebook' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            $validated['image'] = $this->processAndStoreImage($request->file('image_file'));
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
            'image_file' => 'nullable|image|max:5120',
            'category' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'status' => 'required|in:draft,published,archived',
            'publish_to_facebook' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            if ($news->image) {
                $oldPath = str_replace('/storage/', '', $news->image);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['image'] = $this->processAndStoreImage($request->file('image_file'));
        }

        $news->update($validated);

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

    private function processAndStoreImage($file): string
    {
        $filename = 'news_' . time() . '_' . uniqid() . '.webp';
        $path = 'news/' . $filename;

        // Optimize using Intervention Image
        $img = \Intervention\Image\Laravel\Facades\Image::read($file);

        // Resize to a maximum width of 1200px
        if ($img->width() > 1200) {
            $img->scale(width: 1200);
        }

        $encoded = $img->toWebp(80);
        
        Storage::disk('public')->put($path, (string) $encoded);

        return Storage::url($path);
    }
}
