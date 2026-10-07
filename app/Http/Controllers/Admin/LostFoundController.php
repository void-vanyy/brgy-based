<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LostFound;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LostFoundController extends Controller
{
    public function index(Request $request): View
    {
        $query = LostFound::query()->with('reporter:id,name');

        if (in_array($request->input('status'), array_keys(LostFound::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('item_name', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%')
                ->orWhere('location', 'like', '%'.$term.'%'));
        }

        $items = $query->latest()->get();

        $counts = [
            'all' => LostFound::count(),
            'lost' => LostFound::where('status', 'lost')->count(),
            'found' => LostFound::where('status', 'found')->count(),
            'claimed' => LostFound::where('status', 'claimed')->count(),
        ];

        return view('admin.lost-found.index', [
            'items' => $items,
            'counts' => $counts,
            'active' => in_array($request->input('status'), array_keys(LostFound::STATUSES), true)
                ? $request->input('status')
                : 'all',
        ]);
    }

    public function create(): View
    {
        return view('admin.lost-found.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        LostFound::create([
            ...$data,
            'reported_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.lost-found.index')
            ->with('success', '"'.$data['item_name'].'" was added to the lost and found board.');
    }

    public function edit(LostFound $lostFound): View
    {
        return view('admin.lost-found.edit', ['item' => $lostFound]);
    }

    public function update(Request $request, LostFound $lostFound): RedirectResponse
    {
        $lostFound->update($this->validated($request));

        return redirect()
            ->route('admin.lost-found.index')
            ->with('success', '"'.$lostFound->item_name.'" was updated.');
    }

    public function destroy(LostFound $lostFound): RedirectResponse
    {
        $name = $lostFound->item_name;
        $lostFound->delete();

        return redirect()
            ->route('admin.lost-found.index')
            ->with('success', '"'.$name.'" was removed from the board.');
    }

    /** Quick status switch from the board (lost / found / claimed). */
    public function status(Request $request, LostFound $lostFound): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(LostFound::STATUSES))],
        ]);

        $lostFound->update(['status' => $data['status']]);

        return redirect()
            ->route('admin.lost-found.index', ['status' => $data['status']])
            ->with('success', '"'.$lostFound->item_name.'" is now marked as '.LostFound::STATUSES[$data['status']].'.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'item_name' => ['required', 'string', 'max:140'],
            'category' => ['required', 'string', Rule::in(array_keys(LostFound::CATEGORIES))],
            'description' => ['required', 'string', 'min:10', 'max:4000'],
            'status' => ['required', Rule::in(array_keys(LostFound::STATUSES))],
            'location' => ['required', 'string', 'max:190'],
            'date_occurred' => ['nullable', 'date'],
            'contact_info' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'string', 'max:400'],
        ]);
    }
}
