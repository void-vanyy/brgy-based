<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Job;
use App\Models\LostFound;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::query()->published()->take(4)->get();
        $jobs = Job::query()->where('status', 'open')->latest()->take(3)->get();
        $lostFound = LostFound::query()->whereIn('status', ['lost', 'found'])->latest()->take(3)->get();

        return view('home', [
            'announcements' => $announcements,
            'jobs' => $jobs,
            'lostFound' => $lostFound,
            'settings' => Setting::kv(),
        ]);
    }

    public function features(): View
    {
        return view('features', ['settings' => Setting::kv()]);
    }
}
