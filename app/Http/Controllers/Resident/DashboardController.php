<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\QueueTicket;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $id = (int) $user->id;

        $openComplaints = Complaint::mine($id)
            ->whereIn('status', ['received', 'in_progress', 'on_hold'])
            ->count();

        $activeDocuments = DocumentRequest::where('user_id', $id)
            ->whereIn('status', ['pending', 'under_review', 'approved', 'ready_for_release'])
            ->count();

        $upcoming = Appointment::where('user_id', $id)
            ->whereDate('appointment_date', '>=', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->get();

        $myTicket = QueueTicket::forDate()
            ->where('user_id', $id)
            ->whereIn('status', ['waiting', 'called', 'serving'])
            ->first();

        $barangay = [
            'name' => Setting::get('barangay_name', config('app.name')),
            'address' => Setting::get('barangay_address', 'Barangay Hall, Sigla Street'),
            'contact' => Setting::get('barangay_contact', '(02) 8123-4567'),
            'captain' => Setting::get('barangay_capain', Setting::get('barangay_captain', 'Hon. Juan Dela Cruz')),
            'motto' => Setting::get('barangay_motto', 'Malasakit, Serbisyo, Pagkakaisa.'),
        ];

        return view('resident.dashboard', [
            'user' => $user,
            'openComplaints' => $openComplaints,
            'activeDocuments' => $activeDocuments,
            'upcomingAppointments' => $upcoming,
            'nextAppointment' => $upcoming->first(),
            'myTicket' => $myTicket,
            'announcements' => Announcement::published()->limit(5)->get(),
            'complaints' => Complaint::mine($id)->latest()->limit(3)->get(),
            'statuses' => ComplaintController::STATUSES,
            'categories' => ComplaintController::CATEGORIES,
            'barangay' => $barangay,
        ]);
    }
}
