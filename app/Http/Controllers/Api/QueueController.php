<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class QueueController extends Controller
{
    /**
     * GET /api/queue/status
     *
     * Live snapshot of today's queue for the lobby board and the portal widget.
     */
    public function status(): JsonResponse
    {
        $tickets = QueueTicket::forDate(Carbon::today())
            ->orderBy('id')
            ->get();

        $nowServing = QueueTicket::forDate(Carbon::today())
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'now_serving' => $nowServing?->ticket_no,
            'window' => $nowServing?->window,
            'waiting_count' => $tickets->where('status', 'waiting')->count(),
            'called_count' => $tickets->where('status', 'called')->count(),
            'done_count' => $tickets->where('status', 'done')->count(),
            'tickets' => $tickets->map(fn (QueueTicket $ticket): array => [
                'ticket_no' => $ticket->ticket_no,
                'service' => $ticket->service,
                'status' => $ticket->status,
                'window' => $ticket->window,
            ])->values(),
        ]);
    }
}
