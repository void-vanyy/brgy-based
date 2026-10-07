<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /** Offices a resident can book time with. */
    public const OFFICES = [
        'barangay_hall' => 'Barangay Hall',
        'health_center' => 'Health Center',
        'peace_order_desk' => 'Peace & Order Desk',
        'sk_office' => 'SK Office',
    ];

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', '');
        if (! array_key_exists($status, Appointment::STATUSES)) {
            $status = '';
        }

        $query = Appointment::where('user_id', auth()->id())->orderByDesc('appointment_date');

        if ($status !== '') {
            $query->where('status', $status);
        }

        $counts = ['all' => Appointment::where('user_id', auth()->id())->count()];
        foreach (array_keys(Appointment::STATUSES) as $key) {
            $counts[$key] = Appointment::where('user_id', auth()->id())->where('status', $key)->count();
        }

        return view('resident.appointments.index', [
            'appointments' => $query->paginate(10)->withQueryString(),
            'counts' => $counts,
            'statuses' => Appointment::STATUSES,
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        return view('resident.appointments.create', [
            'offices' => self::OFFICES,
            'slots' => Appointment::SLOTS,
            'today' => today()->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'office' => ['required', 'string', Rule::in(array_keys(self::OFFICES))],
            'purpose' => ['required', 'string', 'min:3', 'max:300'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', Rule::in(Appointment::SLOTS)],
        ], [
            'appointment_date.after_or_equal' => 'Please pick today or a future date.',
        ]);

        $clash = Appointment::where('user_id', $request->user()->id)
            ->whereDate('appointment_date', $data['appointment_date'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($clash) {
            return back()
                ->with('error', 'You already hold a pending or confirmed appointment on that date. Cancel it first or choose another day.')
                ->withInput();
        }

        $appointment = Appointment::create([
            'reference_no' => 'APT-'.str_pad((string) (Appointment::max('id') + 1), 4, '0', STR_PAD_LEFT),
            'user_id' => $request->user()->id,
            'subject' => trim($data['subject']),
            'office' => self::OFFICES[$data['office']],
            'purpose' => trim($data['purpose']),
            'appointment_date' => $data['appointment_date'],
            'time_slot' => $data['time_slot'],
            'status' => 'pending',
        ]);

        return redirect()
            ->route('resident.appointments.index')
            ->with('success', 'Appointment '.$appointment->reference_no.' booked for '.now()->parse($data['appointment_date'])->format('M d, Y').' ('.$data['time_slot'].'). Awaiting confirmation.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->user_id === auth()->id(), 403);

        if (! in_array($appointment->status, ['pending', 'confirmed'], true)) {
            return back()->with('error', 'Only pending or confirmed appointments can be cancelled.');
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()
            ->route('resident.appointments.index')
            ->with('success', 'Appointment '.$appointment->reference_no.' has been cancelled.');
    }
}
