<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QueueController extends Controller
{
    /** The single display board on the wall plus the per-window counters. */
    public const WINDOWS = ['Window 1', 'Window 2', 'Window 3', 'Window 4'];

    public function index(): View
    {
        $nowServing = QueueTicket::forDate()
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->first();

        $waiting = QueueTicket::forDate()->where('status', 'waiting')->orderBy('id')->get();
        $active = QueueTicket::forDate()
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->get();
        $finished = QueueTicket::forDate()
            ->whereIn('status', ['done', 'skipped', 'no_show'])
            ->orderByDesc('served_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.queue.index', [
            'nowServing' => $nowServing,
            'waiting' => $waiting,
            'active' => $active,
            'finished' => $finished,
            'windows' => self::WINDOWS,
            'services' => QueueTicket::SERVICES,
            'statuses' => QueueTicket::STATUSES,
            'badge' => [
                'waiting' => 'badge-cyan',
                'called' => 'badge-amber',
                'serving' => 'badge-amber',
                'done' => 'badge-green',
                'skipped' => 'badge-rose',
                'no_show' => 'badge-rose',
            ],
            'doneCount' => $finished->where('status', 'done')->count(),
            'skippedCount' => $finished->whereIn('status', ['skipped', 'no_show'])->count(),
        ]);
    }

    /** Issue a ticket for a resident who walks up to the counter. */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'walkin_name' => ['required', 'string', 'max:120'],
            'walkin_service' => ['required', Rule::in(array_keys(QueueTicket::SERVICES))],
        ]);

        $ticket = QueueTicket::create([
            'ticket_no' => QueueTicket::nextNumber(today()->toDateString()),
            'name_on_ticket' => trim($data['walkin_name']),
            'service' => $data['walkin_service'],
            'status' => 'waiting',
            'queue_date' => today()->toDateString(),
        ]);

        return redirect()
            ->route('admin.queue.index')
            ->with('success', $ticket->ticket_no.' was issued to '.$ticket->name_on_ticket.'.');
    }

    /** Pull the oldest waiting ticket, announce it, and return the fresh board state. */
    public function callNext(Request $request): JsonResponse
    {
        $data = $request->validate([
            'window' => ['nullable', 'string', 'max:40'],
        ]);

        $ticket = QueueTicket::forDate()
            ->where('status', 'waiting')
            ->orderBy('id')
            ->first();

        if (! $ticket) {
            return response()->json([
                'message' => 'Nobody is waiting in line right now.',
            ], 404);
        }

        $ticket->update([
            'status' => 'called',
            'called_at' => $ticket->called_at ?? now(),
            /* 'window' is nullable, so the key is absent from $data when not posted. */
            'window' => ($data['window'] ?? null) ?: 'Window 1',
        ]);

        $waitingCount = QueueTicket::forDate()->where('status', 'waiting')->count();

        $payload = $ticket->fresh()->toArray();
        $payload['service_name'] = $ticket->serviceName();
        $payload['status_label'] = $ticket->statusLabel();

        return response()->json([
            'ticket' => $payload,
            'now_serving' => $ticket->ticket_no,
            'window' => $ticket->window,
            'waiting_count' => $waitingCount,
        ]);
    }

    public function update(Request $request, QueueTicket $queueTicket): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['called', 'serving', 'done', 'skipped', 'no_show'])],
            'window' => ['nullable', 'string', 'max:40'],
        ]);

        $queueTicket->status = $data['status'];

        if ($data['status'] === 'called') {
            $queueTicket->called_at = $queueTicket->called_at ?? now();

            if ($request->filled('window')) {
                $queueTicket->window = $data['window'];
            }
        }

        if ($data['status'] === 'done') {
            $queueTicket->served_at = now();
        }

        $queueTicket->save();

        if ($request->expectsJson()) {
            return response()->json([
                'ticket' => $queueTicket,
                'waiting_count' => QueueTicket::forDate()->where('status', 'waiting')->count(),
            ]);
        }

        return redirect()
            ->route('admin.queue.index')
            ->with('success', $queueTicket->ticket_no.' is now "'.$queueTicket->statusLabel().'".');
    }

    /** Wipe every ticket issued today and start the board fresh. */
    public function reset(): RedirectResponse
    {
        $removed = QueueTicket::forDate()->delete();

        return redirect()
            ->route('admin.queue.index')
            ->with('success', 'Queue reset — '.$removed.' ticket(s) cleared from today\'s board.');
    }
}
