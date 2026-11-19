@section('title', 'Page expired — '.config('app.name'))

{{-- 419 Page Expired.

     Laravel's stock answer is a bare "Page Expired" and no way out of it,
     which reads as a broken site: the person has just been told nothing
     happened and is left to guess the URL they came from. This page exists to
     give them the three things that actually recover the situation - open the
     form again (a fresh page carries a fresh token, so the resubmit works),
     sign in, or create an account.

     Deliberately standalone, like errors.unavailable: it must render when the
     session itself is the thing that just failed, so it may not lean on a
     layout that reads the session, the database or the auth state. The one
     exception is auth()->check(), which is safe here - the session was started
     before CSRF ran, or there would be no token to mismatch. --}}

@push('styles')
<style>
    .exp-shell {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding: 1.25rem;
    }

    .exp-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        max-width: 46rem;
        width: 100%;
        margin: 0 auto;
    }

    .exp-body {
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .exp-card {
        width: 100%;
        max-width: 34rem;
        background: var(--panel);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        text-align: center;
    }

    .exp-mark {
        width: 54px; height: 54px;
        margin: 0 auto 1.1rem;
        display: flex; align-items: center; justify-content: center;
        border-radius: 15px;
        background: rgba(251, 191, 36, .12);
        color: var(--warning, var(--amber, #fbbf24));
    }

    .exp-card h1 { font-size: 1.28rem; margin: 0 0 .55rem; }
    .exp-card p { color: var(--muted); line-height: 1.65; margin: 0 0 1.3rem; }

    .exp-facts {
        display: grid;
        gap: .4rem;
        text-align: left;
        margin: 0 0 1.3rem;
        padding: .85rem 1rem;
        background: var(--panel-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
    }
    .exp-facts div { display: flex; justify-content: space-between; gap: 1rem; font-size: .78rem; }
    .exp-facts .k { color: var(--dim); }
    .exp-facts .v { font-family: var(--mono); color: var(--text); word-break: break-all; text-align: right; }

    .exp-actions { display: grid; gap: .6rem; }
    .exp-more {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .8rem;
        flex-wrap: wrap;
        margin-top: 1.1rem;
        font-size: .85rem;
        color: var(--muted);
    }

    .exp-why {
        margin-top: 1.2rem;
        padding: .75rem .9rem;
        border: 1px dashed var(--border);
        border-radius: var(--radius-sm);
        font-family: var(--mono);
        font-size: .68rem;
        line-height: 1.55;
        color: var(--dim);
        text-align: left;
        word-break: break-word;
    }
</style>
@endpush

<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>

<div class="exp-shell">

    <header class="exp-top">
        <a href="{{ route('home') }}" class="row items-center" style="gap:.7rem">
            <span class="brand-mark" style="width:34px;height:34px;font-size:.82rem;border-radius:10px">BS</span>
            <span>
                <span class="brand-name" style="display:block">{{ config('app.name') }}</span>
                <span class="brand-sub">Barangay e-Governance</span>
            </span>
        </a>

        {{-- Same reason the auth screens carry it: someone may need larger
             text or higher contrast just to read this page. --}}
        <x-access />
    </header>

    <main class="exp-body">
        <section class="exp-card">
            <div class="exp-mark">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <polyline points="12 7 12 12 15.5 14"></polyline>
                </svg>
            </div>

            <h1>Page expired</h1>
            <p>
                That submission never went through. The page sat open past the end of your
                session — or you signed in again in another tab — so the security check no
                longer matched. <strong>Nothing was sent, so nothing was lost.</strong>
                Opening the page again is enough: the fresh page carries a fresh token.
            </p>

            <div class="exp-facts">
                <div><span class="k">Status</span> <span class="v">419 Page Expired</span></div>
                <div><span class="k">Attempted</span> <span class="v">{{ $method ?? 'POST' }} {{ url()->current() }}</span></div>
                <div><span class="k">Time</span> <span class="v">{{ now()->format('M d, Y · h:i:s A') }}</span></div>
            </div>

            <div class="exp-actions">
                {{-- referrer is the form that just failed. Navigating there is a
                     GET, so it re-renders with a new token - unlike history.back(),
                     which replays the same rejected POST from bfcache. --}}
                <button type="button" class="btn btn-primary btn-block btn-lg"
                        onclick="location.href = document.referrer || '{{ route('home') }}'">
                    Open that page again
                </button>
            </div>

            <div class="exp-more">
                @if (auth()->check())
                    {{-- /login would bounce an authenticated user anyway, so offer
                         the destination directly rather than a link that looks
                         like it does nothing. --}}
                    <a href="{{ auth()->user()->isOfficial() ? route('admin.dashboard') : route('resident.dashboard') }}">
                        Back to my dashboard
                    </a>
                    <span aria-hidden="true">&middot;</span>
                @else
                    <a href="{{ route('login') }}">Sign in</a>
                    <span aria-hidden="true">&middot;</span>
                    <a href="{{ route('register') }}">Create an account</a>
                    <span aria-hidden="true">&middot;</span>
                @endif

                <a href="{{ route('home') }}">Home</a>
            </div>

            {{-- Only surfaced when APP_DEBUG is on, so the group still sees the
                 exact wording during development without leaking it in production.
                 The durable record of every occurrence is in storage/logs. --}}
            @if (! empty($reason))
                <div class="exp-why">Diagnostic: {{ $reason }}</div>
            @endif
        </section>
    </main>
</div>

</body>
</html>
