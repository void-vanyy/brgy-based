<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /** Complaint categories — mirrors the wording used on the intake form. */
    public const CATEGORIES = [
        'peace_and_order' => 'Peace & Order',
        'health' => 'Health & Sanitary Concern',
        'sanitation' => 'Garbage & Sanitation',
        'infrastructure' => 'Roads & Infrastructure',
        'traffic' => 'Traffic & Transport',
        'noise' => 'Noise Disturbance',
        'other' => 'Other Concern',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const STATUSES = [
        'received' => 'Received',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        $category = (string) $request->query('category', '');
        $q = trim((string) $request->query('q', ''));

        if (! array_key_exists($status, self::STATUSES)) {
            $status = '';
        }
        if (! array_key_exists($category, self::CATEGORIES)) {
            $category = '';
        }

        $mine = Complaint::mine((int) auth()->id());

        $counts = ['all' => (clone $mine)->count()];
        foreach (array_keys(self::STATUSES) as $key) {
            $counts[$key] = (clone $mine)->where('status', $key)->count();
        }

        $query = Complaint::mine((int) auth()->id())->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }
        if ($category !== '') {
            $query->where('category', $category);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('reference_no', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }

        return view('resident.complaints.index', [
            'complaints' => $query->paginate(10)->withQueryString(),
            'counts' => $counts,
            'statuses' => self::STATUSES,
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'status' => $status,
            'category' => $category,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('resident.complaints.create', [
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:15', 'max:2000'],
            'category' => ['required', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'location' => ['required', 'string', 'max:190'],
            'priority' => ['required', 'string', Rule::in(array_keys(self::PRIORITIES))],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $complaint = Complaint::create([
            'reference_no' => 'CMP-'.str_pad((string) (Complaint::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'user_id' => $request->user()->id,
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'category' => $data['category'],
            'location' => trim($data['location']),
            'purok' => $request->user()->purok,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'priority' => $data['priority'],
            'status' => 'received',
        ]);

        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'status' => 'received',
            'title' => 'Complaint received',
            'note' => 'Your concern has been logged in the barangay records and is queued for assessment.',
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('resident.complaints.show', $complaint)
            ->with('success', 'Complaint '.$complaint->reference_no.' filed successfully. Keep this reference number for follow-ups.');
    }

    public function show(Complaint $complaint): View
    {
        abort_unless($complaint->user_id === auth()->id(), 403);

        $complaint->load(['assignee', 'resident']);

        $updates = $complaint->updates()->with('author')->orderByDesc('id')->get();

        return view('resident.complaints.show', [
            'complaint' => $complaint,
            'updates' => $updates,
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
        ]);
    }
}
