<?php

namespace App\Http\Controllers\Resident;

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
        $status = (string) $request->query('status', '');
        if (! in_array($status, ['lost', 'found'], true)) {
            $status = '';
        }

        $q = trim((string) $request->query('q', ''));

        $query = LostFound::latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('item_name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }

        return view('resident.lost-found.index', [
            'items' => $query->paginate(9)->withQueryString(),
            'categories' => LostFound::CATEGORIES,
            'statuses' => LostFound::STATUSES,
            'status' => $status,
            'q' => $q,
            'counts' => [
                'all' => LostFound::count(),
                'lost' => LostFound::where('status', 'lost')->count(),
                'found' => LostFound::where('status', 'found')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', Rule::in(array_keys(LostFound::CATEGORIES))],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'status' => ['required', 'string', Rule::in(['lost', 'found'])],
            'location' => ['required', 'string', 'max:190'],
            'date_occurred' => ['required', 'date', 'before_or_equal:today'],
            'contact_info' => ['required', 'string', 'max:120'],
        ]);

        LostFound::create($data + [
            'reported_by' => $request->user()->id,
        ]);

        $label = $data['status'] === 'lost' ? 'lost item report' : 'found item report';

        return back()->with('success', 'Thanks — your '.$label.' is now listed on the Lost & Found board.');
    }
}
