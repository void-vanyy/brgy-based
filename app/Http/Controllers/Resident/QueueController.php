<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\QueueTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(): View
    {
        $mine = QueueTicket::forDate()
            ->where('user_id', auth()->id())
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->latest()
            ->first();

        $nowServing = QueueTicket::forDate()
            ->whereIn('status', ['called', 'serving'])
            ->orderByDesc('called_at')
            ->first();

        $waiting = QueueTicket::forDate()
            ->where('status', 'waiting')
            ->orderBy('id')
            ->get();

        return view('resident.queue.index', [
            'mine' => $mine,
            'nowServing' => $nowServing,
            'waiting' => $waiting,
            'waitingCount' => $waiting->count(),
            'services' => QueueTicket::SERVICES,
            'today' => today()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service' => ['required', 'string', Rule::in(array_keys(QueueTicket::SERVICES))],
        ]);

        $existing = QueueTicket::forDate()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->latest()
            ->first();

        if ($existing) {
            return back()->with('error', 'You already hold ticket '.$existing->ticket_no.' for today. Wait to be served before taking another.');
        }

        $ticket = QueueTicket::create([
            'ticket_no' => QueueTicket::nextNumber(today()->toDateString()),
            'user_id' => $request->user()->id,
            'name_on_ticket' => $request->user()->name,
            'service' => $data['service'],
            'status' => 'waiting',
            'queue_date' => today()->toDateString(),
        ]);

        return back()->with('success', 'Ticket '.$ticket->ticket_no.' issued — you are now in the queue for '.$ticket->serviceName().'.');
    }

    public function leave(): RedirectResponse
    {
        $ticket = QueueTicket::forDate()
            ->where('user_id', auth()->id())
            ->where('status', 'waiting')
            ->latest()
            ->first();

        if (! $ticket) {
            return back()->with('error', 'You have no active queue ticket to give up.');
        }

        $ticket->update(['status' => 'skipped']);

        return back()->with('success', 'You have left the queue. Take a new number anytime.');
    }
}
