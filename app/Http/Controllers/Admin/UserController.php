<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use ScopesAdminQueries;

    public const ROLES = [
        'admin' => 'Administrator',
        'staff' => 'Barangay Staff',
        'resident' => 'Resident',
    ];

    /** Reserved for the city account — only a city admin may assign it. */
    public const CITY_ROLE = 'city_admin';

    public const STATUSES = [
        'active' => 'Active',
        'suspended' => 'Suspended',
    ];

    public function index(Request $request): View
    {
        $query = $this->scopedUsers(User::query())->withCount([
            'complaints',
            'documentRequests as requests_count',
            'appointments as appointments_count',
        ]);

        if ($request->filled('q')) {
            $term = trim($request->string('q')->toString());
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')
                ->orWhere('purok', 'like', '%'.$term.'%'));
        }

        if (in_array($request->input('role'), array_keys(self::ROLES), true)) {
            $query->where('role', $request->input('role'));
        }

        if (in_array($request->input('status'), array_keys(self::STATUSES), true)) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'counts' => [
                'all' => $this->scopedUsers(User::query())->count(),
                'resident' => $this->scopedUsers(User::query())->where('role', 'resident')->count(),
                'staff' => $this->scopedUsers(User::query())->where('role', 'staff')->count(),
                'admin' => $this->scopedUsers(User::query())->where('role', 'admin')->count(),
                'suspended' => $this->scopedUsers(User::query())->where('status', 'suspended')->count(),
            ],
            'scopeLabel' => $this->scopeLabel(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        $this->guardTarget($user, $me);

        $roles = $me->isCityAdmin()
            ? array_merge(self::ROLES, [self::CITY_ROLE => 'City Administrator'])
            : self::ROLES;

        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys($roles))],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        if ($user->is($me)) {
            if ($data['status'] === 'suspended') {
                return $this->deny('You cannot suspend your own account.');
            }

            if ($data['role'] !== $user->role) {
                return $this->deny('You cannot change your own role. Ask another administrator to do it.');
            }
        }

        if ($this->isLastOfficial($user, $data['role'])) {
            return $this->deny('This is the last administrator account in the barangay — promote another admin first.');
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', $user->name.' is now '.self::ROLES[$data['role']].' — '.self::STATUSES[$data['status']].'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        $this->guardTarget($user, $me);

        if ($user->is($me)) {
            return $this->deny('You cannot delete your own account.');
        }

        if ($this->isLastOfficial($user, self::ROLES['resident'])) {
            return $this->deny('This is the last administrator account in the barangay — promote another admin first.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', $name.' was removed from the roster.');
    }

    /**
     * A barangay admin may only act on accounts inside their own barangay; the
     * city admin sits above all 18. A 404 rather than a 403, so an official
     * cannot use the response to confirm that an account exists elsewhere.
     */
    private function guardTarget(User $target, User $me): void
    {
        if ($me->isCityAdmin()) {
            return;
        }

        $id = $this->scopeBarangayId();

        if ($id !== null && $target->barangay_id !== $id) {
            abort(404);
        }
    }

    /**
     * True when the change would leave the barangay with no administrator.
     *
     * Counted inside the official's own scope, so the Tagapo admin cannot
     * demote the Labas admin into leaving Labas unmanaged.
     */
    private function isLastOfficial(User $user, string $newRole): bool
    {
        if ($user->role !== 'admin' || $newRole === 'admin') {
            return false; // never an admin, or staying one.
        }

        return $this->scopedUsers(User::query())
            ->where('role', 'admin')
            ->whereKeyNot($user->id)
            ->count() === 0;
    }

    private function deny(string $message): RedirectResponse
    {
        return redirect()->route('admin.users.index')->with('error', $message);
    }
}
