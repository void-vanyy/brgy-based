<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Narrows every admin query down to the signed-in official's barangay.
 *
 * Three shapes of data exist in this system, and each needs a different
 * predicate:
 *
 *   1. Tables that carry their own barangay_id (complaints, announcements,
 *      jobs, lost & found). Use scoped() or scopedVisible().
 *
 *   2. Resident-owned transactions (document requests, appointments, queue
 *      tickets) which deliberately do NOT repeat barangay_id — the owning
 *      resident already carries it, and duplicating it would let the two
 *      drift apart. Use scopedByResident().
 *
 *   3. The user roster itself, which is scoped by its own barangay_id.
 *      Use scoped() directly.
 *
 * A city_admin has a null barangay_id and therefore sees all 18 barangays.
 * A barangay admin or staff member sees only their own.
 *
 * scopedVisible() additionally keeps rows published city-wide (a null
 * barangay_id) visible to a barangay official, because that is precisely who
 * the city published them for.
 */
trait ScopesAdminQueries
{
    /**
     * The barangay this official is restricted to, or null when they are a
     * city admin (or otherwise not bound to one).
     */
    protected function scopeBarangayId(): ?int
    {
        $user = auth()->user();

        return $user instanceof User && $user->isBarangayScoped()
            ? $user->barangay_id
            : null;
    }

    protected function seesEveryBarangay(): bool
    {
        return $this->scopeBarangayId() === null;
    }

    /**
     * Human-readable name of the scope in force, for the admin header, so an
     * official always knows which barangay's data is on screen.
     */
    protected function scopeLabel(): string
    {
        $user = auth()->user();

        if ($this->seesEveryBarangay()) {
            return 'All 18 barangays — Santa Rosa City';
        }

        return $user?->barangay?->name ?? 'Unassigned barangay';
    }

    /**
     * Exact-match scoping for a table that owns a barangay_id column.
     */
    protected function scoped(Builder $query, string $column = 'barangay_id'): Builder
    {
        $id = $this->scopeBarangayId();

        return $id === null ? $query : $query->where($column, $id);
    }

    /**
     * Scoping for published content: the official's own barangay plus
     * anything carrying a null barangay_id (city-wide).
     */
    protected function scopedVisible(Builder $query, string $column = 'barangay_id'): Builder
    {
        $id = $this->scopeBarangayId();

        if ($id === null) {
            return $query;
        }

        return $query->where(
            fn (Builder $inner) => $inner->where($column, $id)->orWhereNull($column)
        );
    }

    /**
     * Scoping for a transaction table that reaches its barangay through the
     * resident who filed it.
     */
    protected function scopedByResident(Builder $query, string $relation = 'resident'): Builder
    {
        $id = $this->scopeBarangayId();

        if ($id === null) {
            return $query;
        }

        return $query->whereHas(
            $relation,
            fn (Builder $inner) => $inner->where('barangay_id', $id)
        );
    }

    /**
     * Scoping for a raw query that selects from users (or a table with the
     * same column) without going through a relation.
     */
    protected function scopedUsers(Builder $query): Builder
    {
        return $this->scoped($query);
    }
}
