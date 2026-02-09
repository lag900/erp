<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdministrationUnit;
use App\Models\Department;
use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $news = News::where('status', 'published')
            ->orderBy('publish_date', 'desc')
            ->take(3)
            ->get();

        $administrations = AdministrationUnit::where('status', 'active')
            ->orderBy('display_order')
            ->get();

        $faculties = Department::where('status', 'active')
            ->orderBy('display_order')
            ->get();

        $mediaSettings = \App\Models\MediaSetting::first();

        return Inertia::render('Welcome', [
            'initialNews' => $news,
            'administrations' => $administrations,
            'faculties' => $faculties,
            'mediaSettings' => $mediaSettings,
        ]);
    }
}
