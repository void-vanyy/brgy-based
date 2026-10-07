<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $category = (string) $request->query('category', '');

        $categories = Announcement::published()
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        if (! $categories->contains($category)) {
            $category = '';
        }

        $query = Announcement::published();

        if ($category !== '') {
            $query->where('category', $category);
        }

        return view('resident.announcements.index', [
            'announcements' => $query->paginate(9)->withQueryString(),
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function show(Announcement $announcement): View
    {
        abort_unless($announcement->is_published, 404);

        return view('resident.announcements.show', [
            'announcement' => $announcement,
        ]);
    }
}
