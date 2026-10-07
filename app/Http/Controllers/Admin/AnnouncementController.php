<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /** Kept in sync with the categories documented in the announcements migration. */
    public const CATEGORIES = [
        'information' => 'Information',
        'event' => 'Events & Activities',
        'emergency' => 'Emergency Advisory',
        'program' => 'Programs',
        'health' => 'Health & Sanitation',
        'ordinance' => 'Ordinances & Resolutions',
    ];

    public function index(Request $request): View
    {
        $query = Announcement::query()->with('creator:id,name');

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$term.'%')
                ->orWhere('body', 'like', '%'.$term.'%'));
        }

        if (in_array($request->input('category'), array_keys(self::CATEGORIES), true)) {
            $query->where('category', $request->input('category'));
        }

        if (in_array($request->input('published'), ['published', 'draft'], true)) {
            $query->where('is_published', $request->input('published') === 'published');
        }

        $announcements = $query->orderByDesc('is_pinned')->latest()->get();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'publishedCount' => Announcement::where('is_published', true)->count(),
            'draftCount' => Announcement::where('is_published', false)->count(),
            'pinnedCount' => Announcement::where('is_pinned', true)->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = Announcement::create([
            ...$data,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement "'.$announcement->title.'" was saved.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', ['announcement' => $announcement]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validated($request);

        $published = $request->boolean('is_published');

        $announcement->update([
            ...$data,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => $published,
            'published_at' => $published ? ($announcement->published_at ?? now()) : null,
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement "'.$announcement->title.'" was updated.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement "'.$title.'" was deleted.');
    }

    /** Public-facing rendering used to check the card before publishing. */
    public function preview(Announcement $announcement): View
    {
        return view('admin.announcements.preview', ['announcement' => $announcement]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'min:10', 'max:8000'],
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'event_date' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:190'],
            'is_pinned' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }
}
