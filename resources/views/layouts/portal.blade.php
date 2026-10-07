@php
    $portal = $portal ?? 'resident';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.partials.head')
</head>
<body>

<div class="shell">

    {{-- ============ sidebar ============ --}}
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">BS</div>
            <div>
                <div class="brand-name">{{ config('app.name') }}</div>
                <div class="brand-sub">{{ $portal === 'admin' ? 'Officials Console' : 'Resident Portal' }}</div>
            </div>
        </div>

        @include('layouts.nav.'.$portal)

        <div class="sidebar-foot">
            <a href="{{ route('home') }}" class="nav-item">
                <x-icon name="compass" /> <span>Public website</span>
            </a>
        </div>
    </aside>
    <div class="sidebar-backdrop"></div>

    {{-- ============ main ============ --}}
    <div class="main">
        <header class="topbar">
            <button class="hamburger" data-sidebar-toggle aria-label="Toggle navigation">
                <x-icon name="menu" size="20" />
            </button>

            <div class="relative grow" style="min-width:0">
                <div class="topbar-crumb">{{ $portal === 'admin' ? 'Barangay administration' : 'Resident services' }}</div>
                <div class="topbar-title">@yield('topbar-title', 'Dashboard')</div>
            </div>

            @hasSection('topbar-actions')
                <div class="row shrink-0">@yield('topbar-actions')</div>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="user-pill" title="Sign out">
                    <span class="avatar sm">{{ auth()->user()->initials }}</span>
                    <span class="hidden" style="display:block">
                        <span class="nm">{{ auth()->user()->name }}</span>
                        <span class="rl">{{ auth()->user()->role }}</span>
                    </span>
                    <x-icon name="logout" size="16" />
                </button>
            </form>
        </header>

        <main class="content">
            @if (session('success'))
                <div data-flash="{{ session('success') }}" data-flash-type="success" hidden></div>
            @endif
            @if (session('error'))
                <div data-flash="{{ session('error') }}" data-flash-type="error" hidden></div>
            @endif
            @if ($errors->any())
                <div data-flash="{{ $errors->first() }}" data-flash-type="error" hidden></div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<div id="toasts"></div>
@stack('modals')
</body>
</html>
