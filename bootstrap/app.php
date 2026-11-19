<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));

        /* 'guest' also guards /login and /register, so this decides where an
           already-signed-in person lands when they use the public site's
           "Sign in" or "Create account" links. The framework's default is
           route('home') - the very page they just clicked on - so both links
           appeared dead: click, bounce, nothing happens. Send them where
           sign-in itself would have sent them, so the click always lands. */
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            if (! $user) {
                return route('home');
            }

            return $user->isOfficial() ? route('admin.dashboard') : route('resident.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (Throwable $e) {
            if (! \App\Support\DbOutage::isConnectionFailure($e)) {
                return null; // not an outage — let Laravel handle it normally
            }

            // The database is unreachable. Report 503 rather than 500 so
            // browsers and uptime checks treat it as transient and stop
            // caching a hard failure, and render a page that explains what
            // is happening instead of leaking a stack trace.
            return response()->view('errors.unavailable', [
                'reason' => config('app.debug') ? $e->getMessage() : null,
            ], 503)->header('Retry-After', '30');
        });

        /* 419 Page Expired.

           CSRF failures are on the framework's do-not-report list, so out of
           the box a 419 leaves no trace anywhere: not in the log, not in any
           trace the user can act on. Two things happen because of that - the
           cause stays unidentifiable, and the person is parked on a bare
           "Page Expired" with no way to sign in, create an account, or even
           go back.

           So this does both jobs: write down the handful of facts that
           separate the real causes (no session cookie at all, a foreign
           cookie from the other install sharing this host, a form that never
           carried a token), and hand the browser a page with a way out.

           The type is HttpException, not TokenMismatchException, because
           prepareException() has already wrapped the original in an
           HttpException(419) by the time render callbacks run. */
        $exceptions->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $sessionCookie = (string) config('session.cookie');
            $sent = array_keys($request->cookies->all());

            Log::warning('CSRF token mismatch (419)', [
                'path' => $request->path(),
                'method' => $request->method(),
                'referer' => $request->headers->get('referer'),
                'ajax' => $request->expectsJson() || $request->ajax(),
                'token_in_request' => $request->has('_token'),
                'session_cookie_expected' => $sessionCookie,
                'session_cookie_sent' => in_array($sessionCookie, $sent, true),
                'other_cookies' => array_values(array_diff($sent, ['XSRF-TOKEN', $sessionCookie])),
                'session_token_present' => $request->hasSession() && is_string($request->session()->token()),
                'config_cached' => app()->configurationIsCached(),
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session expired before that was sent, so nothing was saved. Sign in again and retry.',
                ], 419);
            }

            return response()->view('errors.419', [
                'reason' => config('app.debug') ? $e->getMessage() : null,
                'method' => $request->method(),
            ], 419);
        });
    })->create();
