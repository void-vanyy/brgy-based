<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /** Lifecycle of a complaint, in order. */
    public const STATUSES = [
        'received' => 'Received',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    /** Badge class per status — mirrors the site-wide colour convention. */
    public const STATUS_BADGES = [
        'received' => 'badge-cyan',
        'in_progress' => 'badge-amber',
        'on_hold' => 'badge-violet',
        'resolved' => 'badge-green',
        'closed' => 'badge-rose',
    ];

    public const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    public const PRIORITY_BADGES = [
        'low' => 'badge-neutral',
        'medium' => 'badge-blue',
        'high' => 'badge-amber',
        'urgent' => 'badge-rose',
    ];

    public const CATEGORIES = [
        'peace_and_order' => 'Peace & Order',
        'health' => 'Health',
        'sanitation' => 'Sanitation',
        'infrastructure' => 'Infrastructure',
        'traffic' => 'Traffic',
        'noise' => 'Noise Disturbance',
        'other' => 'Other Concern',
    ];

    public function index(Request $request): View
    {
        $query = Complaint::query()->with(['resident:id,name,purok,avatar', 'assignee:id,name']);

        if (in_array($request->input('status'), array_keys(self::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        if (in_array($request->input('priority'), array_keys(self::PRIORITIES), true)) {
            $query->where('priority', $request->input('priority'));
        }

        if (in_array($request->input('category'), array_keys(self::CATEGORIES), true)) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());

            $query->where(function ($q) use ($term) {
                $q->where('reference_no', 'like', '%'.$term.'%')
                    ->orWhere('title', 'like', '%'.$term.'%')
                    ->orWhere('location', 'like', '%'.$term.'%')
                    ->orWhereHas('resident', fn ($r) => $r->where('name', 'like', '%'.$term.'%'));
            });
        }

        $complaints = $query->latest()->paginate(12)->withQueryString();

        $counts = Complaint::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.complaints.index', [
            'complaints' => $complaints,
            'counts' => $counts,
        ]);
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load([
            'resident:id,name,email,phone,purok,address,avatar,role',
            'assignee:id,name,avatar',
            'updates.author:id,name,avatar',
        ]);

        $resident = $complaint->resident;

        $residentStats = [
            'complaints' => $resident ? $resident->complaints()->count() : 0,
            'requests' => $resident ? $resident->documentRequests()->count() : 0,
            'appointments' => $resident ? $resident->appointments()->count() : 0,
        ];

        $staff = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.complaints.show', [
            'complaint' => $complaint,
            'residentStats' => $residentStats,
            'staff' => $staff,
        ]);
    }

    public function update(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'priority' => ['required', Rule::in(array_keys(self::PRIORITIES))],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'admin_remarks' => ['nullable', 'string', 'max:4000'],
        ]);

        $previous = $complaint->status;
        $changed = $previous !== $data['status'];

        $complaint->fill([
            'status' => $data['status'],
            'priority' => $data['priority'],
            'assigned_to' => $data['assigned_to'] ?: null,
            'admin_remarks' => $data['admin_remarks'] ?: null,
        ]);

        /* Keep the resolution stamp on terminal states, clear it when reopened. */
        if (in_array($data['status'], ['resolved', 'closed'], true)) {
            $complaint->resolved_at = $complaint->resolved_at ?? now();
        } else {
            $complaint->resolved_at = null;
        }

        $complaint->save();

        if ($changed) {
            $complaint->updates()->create([
                'status' => $data['status'],
                'title' => sprintf(
                    'Status moved from %s to %s',
                    self::STATUSES[$previous] ?? ucfirst($previous),
                    self::STATUSES[$data['status']]
                ),
                'note' => $data['admin_remarks']
                    ?: 'Case status updated by '.($request->user()->name ?? 'an administrator').'.',
                'updated_by' => $request->user()?->id,
            ]);
        }

        return redirect()
            ->route('admin.complaints.show', $complaint)
            ->with('success', 'Complaint '.$complaint->reference_no.' updated successfully.');
    }

    public function note(Request $request, Complaint $complaint): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:140'],
            'note' => ['required', 'string', 'max:4000'],
        ]);

        $complaint->updates()->create([
            'status' => $complaint->status,
            'title' => $data['title'] ?: 'Progress note added',
            'note' => $data['note'],
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.complaints.show', $complaint)
            ->with('success', 'A new entry was added to the tracker.');
    }
}
