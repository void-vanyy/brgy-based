<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Barangay;
use App\Models\Complaint;
use App\Models\DocumentRequest;
use App\Models\QueueTicket;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Per-barangay workload across all 18 barangays.
 *
 * The city admin's dashboard aggregates everything into one set of numbers,
 * which hides the fact that the workload is rarely spread evenly. This
 * produces the breakdown those views need.
 *
 * Shared by the dashboard (busiest few, as a pulse strip) and the full
 * Barangays overview, so the two can never drift apart.
 *
 * Each table reaches the barangay differently, which is why there is not one
 * query here: complaints and queue tickets carry a barangay_id of their own,
 * while document requests and appointments inherit it from the resident who
 * filed them.
 */
class CityBreakdown
{
    private const OPEN_COMPLAINTS = ['received', 'in_progress', 'on_hold'];
    private const OPEN_DOCS = ['pending', 'under_review'];

    /** @return Collection<int, array<string, mixed>> one row per barangay */
    public static function perBarangay(): Collection
    {
        /* users also carries a status column, so every predicate below names
           its table explicitly once the join is in place. */
        $byBarangay = fn ($query) => $query
            ->groupBy('barangay_id')
            ->selectRaw('barangay_id, COUNT(*) as total')
            ->pluck('total', 'barangay_id');

        $byResidentBarangay = fn ($query, string $table) => $query
            ->join('users', 'users.id', '=', "{$table}.user_id")
            ->groupBy('users.barangay_id')
            ->selectRaw('users.barangay_id as barangay_id, COUNT(*) as total')
            ->pluck('total', 'barangay_id');

        $openComplaints = $byBarangay(
            Complaint::whereIn('status', self::OPEN_COMPLAINTS)
        );
        $urgentComplaints = $byBarangay(
            Complaint::where('priority', 'urgent')->whereIn('status', self::OPEN_COMPLAINTS)
        );
        $totalComplaints = $byBarangay(Complaint::query());
        $resolvedComplaints = $byBarangay(
            Complaint::whereIn('status', ['resolved', 'closed'])
        );
        $pendingDocs = $byResidentBarangay(
            DocumentRequest::whereIn('document_requests.status', self::OPEN_DOCS),
            'document_requests'
        );
        $pendingAppts = $byResidentBarangay(
            Appointment::where('appointments.status', 'pending'),
            'appointments'
        );
        $waiting = $byBarangay(QueueTicket::forDate()->where('status', 'waiting'));
        $residents = $byBarangay(User::where('role', 'resident'));

        /* NB: (int) binds tighter than ??, so the cast has to wrap the lookup -
           otherwise the fallback never runs and the intent is unreadable. */
        $metric = fn ($byId, int $id): int => (int) ($byId[$id] ?? 0);

        return Barangay::ordered()->get()
            ->map(fn (Barangay $barangay) => [
                'id'        => $barangay->id,
                'name'      => $barangay->name,
                'alias'     => $barangay->alias,
                'open'      => $metric($openComplaints, $barangay->id),
                'urgent'    => $metric($urgentComplaints, $barangay->id),
                'total'     => $metric($totalComplaints, $barangay->id),
                'resolved'  => $metric($resolvedComplaints, $barangay->id),
                'docs'      => $metric($pendingDocs, $barangay->id),
                'appts'     => $metric($pendingAppts, $barangay->id),
                'waiting'   => $metric($waiting, $barangay->id),
                'residents' => $metric($residents, $barangay->id),
            ])
            ->sortByDesc('open')
            ->values();
    }

    /** City-wide totals, summed across every barangay. */
    public static function totals(Collection $rows): array
    {
        return [
            'barangays' => $rows->count(),
            'open'      => (int) $rows->sum('open'),
            'urgent'    => (int) $rows->sum('urgent'),
            'docs'      => (int) $rows->sum('docs'),
            'appts'     => (int) $rows->sum('appts'),
            'waiting'   => (int) $rows->sum('waiting'),
            'residents' => (int) $rows->sum('residents'),
        ];
    }

    public static function maxOpen(Collection $rows): int
    {
        return max(1, (int) $rows->max('open'));
    }
}
