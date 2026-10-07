<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public const ROLES = [
        'admin' => 'Administrator',
        'staff' => 'Barangay Staff',
        'resident' => 'Resident',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'suspended' => 'Suspended',
    ];

    public function index(Request $request): View
    {
        $query = User::query()->withCount([
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
                'all' => User::count(),
                'resident' => User::where('role', 'resident')->count(),
                'staff' => User::where('role', 'staff')->count(),
                'admin' => User::where('role', 'admin')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $me = $request->user();

        if ($user->is($me)) {
            if ($data['status'] === 'suspended') {
                return $this->deny('You cannot suspend your own account.');
            }

            if ($data['role'] !== $user->role) {
                return $this->deny('You cannot change your own role. Ask another administrator to do it.');
            }
        }

        if ($user->role === 'admin' && $data['role'] !== 'admin'
            && User::where('role', 'admin')->count() <= 1) {
            return $this->deny('This is the last administrator account — promote another admin first.');
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', $user->name.' is now '.self::ROLES[$data['role']].' — '.self::STATUSES[$data['status']].'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return $this->deny('You cannot delete your own account.');
        }

        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return $this->deny('This is the last administrator account — promote another admin first.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', $name.' was removed from the roster.');
    }

    private function deny(string $message): RedirectResponse
    {
        return redirect()->route('admin.users.index')->with('error', $message);
    }
}
