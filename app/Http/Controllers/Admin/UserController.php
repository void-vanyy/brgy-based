<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RendersStatusPatches;
use App\Http\Controllers\Concerns\ScopesAdminQueries;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    use RendersStatusPatches, ScopesAdminQueries;

    public const ROLES = [
        'admin' => 'Administrator',
        'staff' => 'Barangay Staff',
        'resident' => 'Resident',
    ];

    /** Role -> badge class. Shared by the board and the in-place patch. */
    public const ROLE_BADGES = [
        'admin' => 'badge-violet',
        'staff' => 'badge-blue',
        'resident' => 'badge-neutral',
        'city_admin' => 'badge-neutral',   /* the board previously fell through to neutral */
    ];

    /** Status -> badge class. Shared by the board and the in-place patch. */
    public const STATUS_BADGES = [
        'active' => 'badge-green',
        'suspended' => 'badge-rose',
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

    public function update(Request $request, User $user): RedirectResponse|JsonResponse
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

        $label = ($roles[$data['role']] ?? self::ROLES[$data['role']] ?? $data['role'])
            .' — '.self::STATUSES[$data['status']];
        $message = $user->name.' is now '.$label.'.';

        return $this->savedResponse(
            $request,
            'admin.users.index',
            $message,
            [
                /* The avatar colour follows the role, so the identity cell moves too. */
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-identity-cell]', $user->id),
                    'view' => 'admin.users.partials.identity',
                    'data' => ['user' => $user, 'me' => $me->id],
                ],
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-role-cell]', $user->id),
                    'badge' => self::ROLE_BADGES[$user->role] ?? 'badge-neutral',
                    'label' => $roles[$user->role] ?? self::ROLES[$user->role] ?? ucfirst($user->role),
                ],
                [
                    'selector' => $this->statusCellSelector($user->id),
                    'badge' => self::STATUS_BADGES[$user->status] ?? 'badge-neutral',
                    'label' => self::STATUSES[$user->status] ?? ucfirst($user->status),
                ],
                /* The Manage button prefills the modal from these, so they have
                   to follow the save or the next open offers a stale value. */
                [
                    'selector' => sprintf('[data-record-id="%d"] [data-modal-open="manageUser"]', $user->id),
                    'attrs' => [
                        'data-role' => $user->role,
                        'data-status' => $user->status,
                        'data-name' => $user->name,
                    ],
                ],
            ],
        );
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
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

    /**
     * A refusal the official can read. Ajax gets it as JSON so the toast can
     * say why nothing changed; a plain post gets the usual redirect + flash.
     */
    private function deny(string $message): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->route('admin.users.index')->with('error', $message);
    }
}
