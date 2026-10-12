<?php

namespace App\Models\Concerns;

/**
 * Shared barangay visibility rules for content that can be either
 * barangay-specific or city-wide.
 *
 * A null barangay_id means "posted for the whole city". Announcements, job
 * listings and lost & found reports all work this way, so they share one
 * implementation instead of each re-deriving the OR-null logic.
 *
 * Freedom posts are always scoped to a single wall, so they only use
 * forBarangay().
 */
trait ScopedToBarangay
{
    /**
     * Records addressed to this barangay, plus everything city-wide.
     *
     * Pass null to get the city-wide view only.
     */
    public function scopeVisibleToBarangay($query, ?int $barangayId)
    {
        return $query->where(function ($w) use ($barangayId) {
            $w->where('barangay_id', $barangayId)
                ->orWhereNull('barangay_id');
        });
    }

    /** Strictly this barangay's own records — nothing city-wide. */
    public function scopeForBarangay($query, ?int $barangayId)
    {
        return $query->where('barangay_id', $barangayId);
    }
}
