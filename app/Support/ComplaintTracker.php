<?php

namespace App\Support;

use App\Models\Complaint;

/**
 * Builds the ordered progress-tracker entries shown on a complaint.
 *
 * Lives outside the view so the initial page load and an ajax save that
 * rewrites the tracker in place always agree on what the timeline contains -
 * there is one definition of "the tracker" rather than two that can drift.
 */
class ComplaintTracker
{
    /**
     * @return \Illuminate\Support\Collection<int, array{title: string, status: string, label: string, note: ?string, author: string, at: \Illuminate\Support\Carbon}>
     */
    public static function events(Complaint $complaint): \Illuminate\Support\Collection
    {
        $labels = \App\Http\Controllers\Admin\ComplaintController::STATUSES;

        $events = collect([
            [
                'title' => 'Complaint filed',
                'status' => 'received',
                'label' => 'Received',
                'note' => \Illuminate\Support\Str::limit($complaint->description, 420),
                'author' => $complaint->resident?->name ?? 'Resident',
                'at' => $complaint->created_at,
            ],
        ]);

        foreach ($complaint->updates->sortBy('id') as $update) {
            $events->push([
                'title' => $update->title ?: 'Case updated',
                'status' => $update->status,
                'label' => $labels[$update->status] ?? ucfirst(str_replace('_', ' ', $update->status)),
                'note' => $update->note,
                'author' => $update->author?->name ?? 'Barangay office',
                'at' => $update->created_at,
            ]);
        }

        return $events;
    }
}
