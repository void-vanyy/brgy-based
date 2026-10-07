<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\Job;
use App\Models\QueueTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /* ---- complaint breakdown for the bar chart ---- */
        $statusTotals = Complaint::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $statusTotals->sum();
        $peak = max(1, (int) $statusTotals->max());

        $bars = collect(ComplaintController::STATUSES)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'badge' => ComplaintController::STATUS_BADGES[$key],
                'count' => (int) ($statusTotals[$key] ?? 0),
                'pct' => (int) round(((int) ($statusTotals[$key] ?? 0) / $peak) * 100),
            ])
            ->values();

        $peakBar = $bars->sortByDesc('count')->first() ?? [
            'key' => 'received',
            'label' => ComplaintController::STATUSES['received'] ?? 'Received',
            'count' => 0,
        ];

        /* ---- headline numbers ---- */
        $openComplaints = Complaint::whereIn('status', ['received', 'in_progress', 'on_hold'])->count();
        $pendingDocs = DocumentRequest::whereIn('status', ['pending', 'under_review'])->count();
        $pendingAppts = Appointment::where('status', 'pending')->count();
        $waiting = QueueTicket::forDate()->where('status', 'waiting')->count();
        $residents = User::where('role', 'resident')->count();
        $openJobs = Job::where('status', 'open')->count();

        /* ---- today's queue snapshot ---- */
        $nowServing = QueueTicket::forDate()
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->first();
        $servedToday = QueueTicket::forDate()->where('status', 'done')->count();
        $skippedToday = QueueTicket::forDate()->whereIn('status', ['skipped', 'no_show'])->count();
        $queueLine = QueueTicket::forDate()->where('status', 'waiting')->orderBy('id')->take(3)->get();

        /* ---- recent activity ---- */
        $latestComplaints = Complaint::with(['resident:id,name,purok', 'assignee:id,name'])
            ->latest()
            ->take(5)
            ->get();

        $announcements = Announcement::query()->published()
            ->with('creator:id,name')
            ->take(4)
            ->get();

        $urgent = Complaint::where('priority', 'urgent')
            ->whereIn('status', ['received', 'in_progress', 'on_hold'])
            ->count();

        return view('admin.dashboard', [
            'bars' => $bars,
            'peakBar' => $peakBar,
            'total' => $total,
            'stats' => [
                [
                    'label' => 'Open complaints',
                    'value' => $openComplaints,
                    'tone' => 'rose',
                    'meta' => $urgent.' urgent waiting for action',
                    'icon' => 'alert',
                    'href' => route('admin.complaints.index', ['status' => 'received']),
                ],
                [
                    'label' => 'Document requests',
                    'value' => $pendingDocs,
                    'tone' => 'violet',
                    'meta' => 'Pending review at the records desk',
                    'icon' => 'file',
                    'href' => route('admin.requests.index', ['status' => 'pending']),
                ],
                [
                    'label' => 'Appointments',
                    'value' => $pendingAppts,
                    'tone' => 'amber',
                    'meta' => 'Awaiting confirmation',
                    'icon' => 'calendar',
                    'href' => route('admin.appointments.index', ['status' => 'pending']),
                ],
                [
                    'label' => 'Waiting in queue',
                    'value' => $waiting,
                    'tone' => 'cyan',
                    'meta' => 'Residents served today: '.$servedToday,
                    'icon' => 'ticket',
                    'href' => route('admin.queue.index'),
                ],
                [
                    'label' => 'Registered residents',
                    'value' => $residents,
                    'tone' => 'green',
                    'meta' => 'Accounts in the community roster',
                    'icon' => 'users',
                    'href' => route('admin.users.index', ['role' => 'resident']),
                ],
                [
                    'label' => 'Open job posts',
                    'value' => $openJobs,
                    'tone' => 'blue',
                    'meta' => 'Livelihood listings live now',
                    'icon' => 'briefcase',
                    'href' => route('admin.jobs.index', ['status' => 'open']),
                ],
            ],
            'nowServing' => $nowServing,
            'waiting' => $waiting,
            'servedToday' => $servedToday,
            'skippedToday' => $skippedToday,
            'queueLine' => $queueLine,
            'latestComplaints' => $latestComplaints,
            'announcements' => $announcements,
        ]);
    }
}
