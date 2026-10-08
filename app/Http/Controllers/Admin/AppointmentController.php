<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Appointment::query()->with(['resident:id,name,email,avatar,purok', 'processor:id,name']);

        if (in_array($request->input('status'), array_keys(Appointment::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(function ($q) use ($term) {
                $q->where('reference_no', 'like', '%'.$term.'%')
                    ->orWhere('subject', 'like', '%'.$term.'%')
                    ->orWhere('office', 'like', '%'.$term.'%')
                    ->orWhereHas('resident', fn ($r) => $r->where('name', 'like', '%'.$term.'%'));
            });
        }

        $appointments = $query
            ->orderBy('appointment_date')
            ->orderBy('time_slot')
            ->get()
            ->groupBy(fn ($a) => $a->appointment_date->toDateString());

        $counts = Appointment::query()
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'counts' => $counts,
            'statuses' => Appointment::STATUSES,
            'badge' => [
                'pending' => 'badge-cyan',
                'confirmed' => 'badge-amber',
                'completed' => 'badge-green',
                'cancelled' => 'badge-rose',
                'no_show' => 'badge-rose',
            ],
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Appointment::STATUSES))],
            'remarks' => ['nullable', 'string', 'max:4000'],
        ]);

        $appointment->update([
            'status' => $data['status'],
            'remarks' => ($data['remarks'] ?? null) ?: null,
            'processed_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', $appointment->reference_no.' is now "'.$appointment->statusLabel().'".');
    }
}
