<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MapController extends Controller
{
    use ScopesAdminQueries;

    public function index(): View
    {
        $complaints = $this->scoped(Complaint::query());

        $statusCounts = (clone $complaints)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (clone $complaints)->count();
        $plotted = (clone $complaints)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();

        /* A barangay board opens on that barangay; the city board on the whole city. */
        $barangay = $this->seesEveryBarangay()
            ? null
            : auth()->user()?->barangay;

        [$latitude, $longitude] = $barangay
            ? $barangay->coordinate()
            : Barangay::CITY_CENTER;

        return view('admin.map.index', [
            'statusCounts' => $statusCounts,
            'total' => $total,
            'plotted' => $plotted,
            'unplotted' => $total - $plotted,
            'badges' => ComplaintController::STATUS_BADGES,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $barangay ? Barangay::BARANGAY_ZOOM : Barangay::CITY_ZOOM,
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }
}
