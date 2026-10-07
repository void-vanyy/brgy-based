@php
    $active = fn (string $pattern) => request()->routeIs($pattern) ? 'active' : '';
    $c = $navCounts ?? [];
@endphp

<nav class="nav">
    <div class="nav-label">Command centre</div>

    <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $active('admin.dashboard') }}">
        <x-icon name="grid" /> <span>Dashboard</span>
    </a>

    <div class="nav-label">Case management</div>

    <a href="{{ route('admin.complaints.index') }}" class="nav-item {{ $active('admin.complaints.*') }}">
        <x-icon name="alert" /> <span>Complaints</span>
        <span class="count">{{ $c['open_complaints'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.requests.index') }}" class="nav-item {{ $active('admin.requests.*') }}">
        <x-icon name="file" /> <span>Document Requests</span>
        <span class="count">{{ $c['pending_docs'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.appointments.index') }}" class="nav-item {{ $active('admin.appointments.*') }}">
        <x-icon name="calendar" /> <span>Appointments</span>
        <span class="count">{{ $c['pending_appts'] ?? 0 }}</span>
    </a>

    <a href="{{ route('admin.queue.index') }}" class="nav-item {{ $active('admin.queue.*') }}">
        <x-icon name="ticket" /> <span>Queueing</span>
        <span class="count">{{ $c['waiting'] ?? 0 }}</span>
    </a>

    <div class="nav-label">Publishing</div>

    <a href="{{ route('admin.announcements.index') }}" class="nav-item {{ $active('admin.announcements.*') }}">
        <x-icon name="megaphone" /> <span>Announcements</span>
    </a>

    <a href="{{ route('admin.lost-found.index') }}" class="nav-item {{ $active('admin.lost-found.*') }}">
        <x-icon name="box" /> <span>Lost &amp; Found</span>
    </a>

    <a href="{{ route('admin.jobs.index') }}" class="nav-item {{ $active('admin.jobs.*') }}">
        <x-icon name="briefcase" /> <span>Livelihood &amp; Jobs</span>
    </a>

    <div class="nav-label">Records</div>

    <a href="{{ route('admin.users.index') }}" class="nav-item {{ $active('admin.users.*') }}">
        <x-icon name="users" /> <span>Residents &amp; Staff</span>
    </a>

    <a href="{{ route('admin.map.index') }}" class="nav-item {{ $active('admin.map.*') }}">
        <x-icon name="map" /> <span>Complaint Map</span>
    </a>

    <a href="{{ route('admin.settings.edit') }}" class="nav-item {{ $active('admin.settings.*') }}">
        <x-icon name="sliders" /> <span>Barangay Settings</span>
    </a>
</nav>
