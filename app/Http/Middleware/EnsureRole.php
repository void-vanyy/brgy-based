<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('role:admin,staff')
 *
 * Guests are sent to the login screen; authenticated users without one of the
 * listed roles are redirected to their own dashboard instead of a bare 403.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->status === 'suspended') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been suspended. Please contact the barangay office.']);
        }

        if (! in_array($user->role, $roles, true)) {
            return redirect()->to($user->isAdmin() ? route('admin.dashboard') : route('resident.dashboard'))
                ->with('error', 'You are not allowed to access that page.');
        }

        return $next($request);
    }
}
