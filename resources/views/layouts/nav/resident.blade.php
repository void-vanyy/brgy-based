@php
    $active = fn (string $pattern) => request()->routeIs($pattern) ? 'active' : '';
@endphp

<nav class="nav">
    <div class="nav-label">Overview</div>

    <a href="{{ route('resident.dashboard') }}" class="nav-item {{ $active('resident.dashboard') }}">
        <x-icon name="grid" /> <span>Dashboard</span>
    </a>

    <div class="nav-label">My transactions</div>

    <a href="{{ route('resident.complaints.index') }}" class="nav-item {{ $active('resident.complaints.*') }}">
        <x-icon name="alert" /> <span>Complaints</span>
        <span class="count">{{ $navCounts['complaints'] ?? 0 }}</span>
    </a>

    <a href="{{ route('resident.documents.index') }}" class="nav-item {{ $active('resident.documents.*') }}">
        <x-icon name="file" /> <span>Documents &amp; Certificates</span>
        <span class="count">{{ $navCounts['documents'] ?? 0 }}</span>
    </a>

    <a href="{{ route('resident.appointments.index') }}" class="nav-item {{ $active('resident.appointments.*') }}">
        <x-icon name="calendar" /> <span>Appointments</span>
        <span class="count">{{ $navCounts['appointments'] ?? 0 }}</span>
    </a>

    <a href="{{ route('resident.queue.index') }}" class="nav-item {{ $active('resident.queue.*') }}">
        <x-icon name="ticket" /> <span>Queueing System</span>
    </a>

    <div class="nav-label">Community</div>

    <a href="{{ route('resident.announcements.index') }}" class="nav-item {{ $active('resident.announcements.*') }}">
        <x-icon name="megaphone" /> <span>Announcements</span>
    </a>

    <a href="{{ route('resident.freedom-wall.index') }}" class="nav-item {{ $active('resident.freedom-wall.*') }}">
        <x-icon name="chat" /> <span>Freedom Wall</span>
    </a>

    <a href="{{ route('resident.lost-found.index') }}" class="nav-item {{ $active('resident.lost-found.*') }}">
        <x-icon name="box" /> <span>Lost &amp; Found</span>
    </a>

    <a href="{{ route('resident.jobs.index') }}" class="nav-item {{ $active('resident.jobs.*') }}">
        <x-icon name="briefcase" /> <span>Livelihood &amp; Jobs</span>
    </a>

    <div class="nav-label">Tools</div>

    <a href="{{ route('resident.map.index') }}" class="nav-item {{ $active('resident.map.*') }}">
        <x-icon name="map" /> <span>Barangay Map</span>
    </a>

    <a href="{{ route('resident.profile.edit') }}" class="nav-item {{ $active('resident.profile.*') }}">
        <x-icon name="user" /> <span>My profile</span>
    </a>
</nav>
