<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Support\CityBreakdown;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * City-wide breakdown of every barangay, one section each.
 *
 * This page exists because the city admin's dashboard aggregates all 18 into
 * a single set of numbers - which hides the fact that most of the workload
 * often sits in one or two barangays. Each row here drills into that
 * barangay's boards via ?barangay=<id>.
 */
class BarangayController extends Controller
{
    use ScopesAdminQueries;

    public function index(): View|RedirectResponse
    {
        $blocked = $this->guardCityAdmin();

        if ($blocked !== null) {
            return $blocked;
        }

        /* The per-barangay numbers live in CityBreakdown so the dashboard's
           pulse strip and this page can never disagree. */
        $rows = CityBreakdown::perBarangay();

        return view('admin.barangays.index', [
            'rows'       => $rows,
            'totals'     => CityBreakdown::totals($rows),
            'maxOpen'    => CityBreakdown::maxOpen($rows),
            'focusId'    => Barangay::requestedFilterId(),
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }

    private function guardCityAdmin(): ?RedirectResponse
    {
        if (auth()->user()?->isCityAdmin()) {
            return null;
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('error', 'The city-wide barangay breakdown is managed by the city administrator.');
    }
}
