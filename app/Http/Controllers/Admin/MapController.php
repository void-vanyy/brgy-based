<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MapController extends Controller
{
    public function index(): View
    {
        $statusCounts = Complaint::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $plotted = Complaint::whereNotNull('latitude')->whereNotNull('longitude')->count();

        return view('admin.map.index', [
            'statusCounts' => $statusCounts,
            'total' => Complaint::count(),
            'plotted' => $plotted,
            'unplotted' => Complaint::count() - $plotted,
            'badges' => ComplaintController::STATUS_BADGES,
        ]);
    }
}
