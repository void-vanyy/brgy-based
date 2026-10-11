<!DOCTYPE html>
{{-- Shown when the database is unreachable. Deliberately standalone: an error
     view must not depend on routes, the session or a layout that itself needs
     the database, or it would fail for the same reason. --}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Service temporarily unavailable — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}">
    <style>
        .down-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
        }
        .down-card {
            width: 100%;
            max-width: 30rem;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
        }
        .down-mark {
            width: 54px; height: 54px;
            margin: 0 auto 1.1rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: 15px;
            background: rgba(251, 113, 133, .12);
            color: var(--danger);
        }
        .down-card h1 { font-size: 1.28rem; margin: 0 0 .55rem; }
        .down-card p { color: var(--muted); line-height: 1.65; margin: 0 0 1.4rem; }
        .down-facts {
            display: grid;
            gap: .4rem;
            text-align: left;
            margin: 0 0 1.4rem;
            padding: .85rem 1rem;
            background: var(--panel-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
        }
        .down-facts div { display: flex; justify-content: space-between; gap: 1rem; font-size: .78rem; }
        .down-facts dt, .down-facts .k { color: var(--dim); }
        .down-facts .v { font-family: var(--mono); color: var(--text); }
        .down-why {
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
</head>
<body>
<div class="down-shell">
    <main class="down-card">
        <div class="down-mark">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
            </svg>
        </div>

        <h1>Service temporarily unavailable</h1>
        <p>
            The barangay system cannot reach its database right now, so no records can be
            read or saved. Your data is safe — nothing has been lost. This usually resolves
            itself within a minute.
        </p>

        <div class="down-facts">
            <div><span class="k">Status</span> <span class="v">503 Service Unavailable</span></div>
            <div><span class="k">Last checked</span> <span class="v">{{ now()->format('M d, Y · h:i:s A') }}</span></div>
            <div><span class="k">Automatic retry</span> <span class="v">30 seconds</span></div>
        </div>

        <button type="button" class="btn btn-primary btn-block" onclick="location.reload()">
            Try again now
        </button>

        {{-- Only surfaced when APP_DEBUG is on, so the group still sees the real
             cause during development without leaking it to end users. --}}
        @if (! empty($reason))
            <div class="down-why">Diagnostic: {{ $reason }}</div>
        @endif
    </main>
</div>
</body>
</html>
