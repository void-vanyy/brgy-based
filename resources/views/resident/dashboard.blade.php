@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Dashboard — '.config('app.name'))
@section('topbar-title', 'Dashboard')

@php($badge = fn (string $s) => match ($s) { 'received', 'pending', 'waiting' => 'badge-cyan', 'in_progress', 'under_review', 'confirmed', 'called', 'serving', 'ready_for_release' => 'badge-amber', 'approved', 'resolved', 'done', 'released', 'completed', 'found', 'open' => 'badge-green', 'rejected', 'closed', 'cancelled', 'no_show', 'skipped' => 'badge-rose', 'on_hold', 'claimed' => 'badge-violet', default => 'badge-neutral' })
@php($step = fn (string $s) => match ($s) { 'in_progress', 'on_hold' => 2, 'resolved' => 3, 'closed' => 4, default => 1 })
@php($categoryLabel = fn (string $c) => ucwords(str_replace('_', ' ', $c)))

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Resident portal</span>
            <h1>Kumusta, {{ explode(' ', $user->name)[0] }}!</h1>
            <p class="sub">{{ now()->format('l, F j, Y') }} · {{ $barangay['name'] }}</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.appointments.create') }}" class="btn">
                <x-icon name="calendar" /> Book appointment
            </a>
            <a href="{{ route('resident.complaints.create') }}" class="btn btn-primary">
                <x-icon name="plus" /> File a complaint
            </a>
        </div>
    </div>

    {{-- ============ at a glance ============ --}}
    <div class="stats">
        <div class="stat tone-cyan">
            <div class="stat-label">Open complaints</div>
            <div class="stat-value">{{ $openComplaints }}</div>
            <div class="stat-meta">{{ $openComplaints > 0 ? 'Being tracked by the barangay' : 'No active reports' }}</div>
        </div>

        <div class="stat tone-violet">
            <div class="stat-label">Document requests</div>
            <div class="stat-value">{{ $activeDocuments }}</div>
            <div class="stat-meta">{{ $activeDocuments > 0 ? 'In the records pipeline' : 'Nothing in the pipeline' }}</div>
        </div>

        <div class="stat tone-green">
            <div class="stat-label">Upcoming appointments</div>
            <div class="stat-value">{{ $upcomingAppointments->count() }}</div>
            <div class="stat-meta">
                @if ($nextAppointment)
                    Next: {{ $nextAppointment->appointment_date->format('M d') }} · {{ $nextAppointment->time_slot }}
                @else
                    No booking on the calendar
                @endif
            </div>
        </div>

        <div class="stat tone-amber">
            <div class="stat-label">Queue today</div>
            <div class="stat-value">{{ $myTicket?->ticket_no ?? '—' }}</div>
            <div class="stat-meta">
                @if ($myTicket && $myTicket->status === 'waiting')
                    {{ $myTicket->serviceName() }} · #{{ $myTicket->positionInLine() }} in line · ~{{ $myTicket->waitMinutes() }} min
                @elseif ($myTicket)
                    {{ $myTicket->statusLabel() }} · {{ $myTicket->serviceName() }}
                @else
                    Take a number when you arrive
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-23 mt-3">
        {{-- ============ left column ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="alert" /> My latest complaints</h2>
                    <a href="{{ route('resident.complaints.index') }}" class="btn btn-sm">View all</a>
                </div>

                @if ($complaints->isEmpty())
                    <div class="empty">
                        <div class="ico"><x-icon name="shield" size="26" /></div>
                        <h3>No complaints filed yet</h3>
                        <p>Spotted a broken streetlight, a sanitation issue or a peace-and-order concern? Report it and track it here.</p>
                        <a href="{{ route('resident.complaints.create') }}" class="btn btn-primary">
                            <x-icon name="plus" /> File your first complaint
                        </a>
                    </div>
                @else
                    <div class="list">
                        @foreach ($complaints as $complaint)
                            <div class="list-item">
                                <div class="grow">
                                    <div class="row between">
                                        <a href="{{ route('resident.complaints.show', $complaint) }}" class="bold">{{ $complaint->title }}</a>
                                        <span class="badge {{ $badge($complaint->status) }}">
                                            {{ $statuses[$complaint->status] ?? $complaint->status }}
                                        </span>
                                    </div>

                                    <div class="row tiny dim mt-1">
                                        <span class="mono">{{ $complaint->reference_no }}</span>
                                        <span>&middot;</span>
                                        <span>{{ $categories[$complaint->category] ?? $categoryLabel($complaint->category) }}</span>
                                        <span>&middot;</span>
                                        <span>Filed {{ $complaint->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="steps mt-2">
                                        @for ($i = 1; $i <= 4; $i++)
                                            <span class="st {{ $i <= $step($complaint->status) ? 'on' : '' }}"></span>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="megaphone" /> Latest announcements</h2>
                    <a href="{{ route('resident.announcements.index') }}" class="btn btn-sm">View all</a>
                </div>

                @if ($announcements->isEmpty())
                    <div class="empty">
                        <div class="ico"><x-icon name="megaphone" size="26" /></div>
                        <h3>Nothing new on the board</h3>
                        <p>Barangay advisories, events and public notices will appear here once published.</p>
                    </div>
                @else
                    <div class="list">
                        @foreach ($announcements as $announcement)
                            <div class="list-item">
                                <span class="avatar sm {{ $announcement->is_pinned ? 'amber' : 'neutral' }}">
                                    <x-icon name="megaphone" size="14" />
                                </span>
                                <div class="grow">
                                    <div class="row between">
                                        <a class="bold" href="{{ route('resident.announcements.show', $announcement) }}">
                                            {{ $announcement->title }}
                                        </a>
                                        @if ($announcement->is_pinned)
                                            <span class="badge badge-amber">Pinned</span>
                                        @endif
                                    </div>
                                    <div class="row tiny dim mt-1">
                                        <span class="badge badge-blue badge-plain">{{ $categoryLabel($announcement->category ?? 'general') }}</span>
                                        <span>{{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ right column ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="grid" /> Quick actions</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-2">
                        <a href="{{ route('resident.documents.create') }}" class="btn btn-ghost btn-block">
                            <x-icon name="file" /> Request a document
                        </a>
                        <a href="{{ route('resident.queue.index') }}" class="btn btn-ghost btn-block">
                            <x-icon name="ticket" /> Take a queue number
                        </a>
                        <a href="{{ route('resident.lost-found.index') }}" class="btn btn-ghost btn-block">
                            <x-icon name="box" /> Lost &amp; found
                        </a>
                        <a href="{{ route('resident.jobs.index') }}" class="btn btn-ghost btn-block">
                            <x-icon name="briefcase" /> Livelihood board
                        </a>
                        <a href="{{ route('resident.freedom-wall.index') }}" class="btn btn-ghost btn-block">
                            <x-icon name="chat" /> Freedom wall
                        </a>
                        <a href="{{ route('resident.map.index') }}" class="btn btn-ghost btn-block">
                            <x-icon name="map" /> Barangay map
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="ticket" /> Today at the hall</h2>
                    <span class="badge badge-neutral"><span data-clock>--:--:--</span></span>
                </div>
                <div class="card-body">
                    @if ($myTicket)
                        <div class="queue-row">
                            <span class="qn">{{ $myTicket->ticket_no }}</span>
                            <span class="grow">{{ $myTicket->serviceName() }}</span>
                            <span class="badge {{ $badge($myTicket->status) }}">{{ $myTicket->statusLabel() }}</span>
                        </div>
                        <div class="row mt-2">
                            <span class="small muted">
                                @if ($myTicket->status === 'waiting')
                                    Position #{{ $myTicket->positionInLine() }} &middot; estimated wait ~{{ $myTicket->waitMinutes() }} minutes
                                @else
                                    Please proceed to the assigned window
                                @endif
                            </span>
                        </div>
                        <a href="{{ route('resident.queue.index') }}" class="btn btn-sm btn-block mt-2">Open queue board</a>
                    @else
                        <p class="small muted">You are not in today's queue. Taking a number online saves you a trip to the window.</p>
                        <a href="{{ route('resident.queue.index') }}" class="btn btn-primary btn-block">
                            <x-icon name="ticket" /> Take a number
                        </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="calendar" /> Next appointment</h2>
                </div>
                <div class="card-body">
                    @if ($nextAppointment)
                        <div class="stack-sm">
                            <div class="row between">
                                <strong>{{ $nextAppointment->subject }}</strong>
                                <span class="badge {{ $badge($nextAppointment->status) }}">{{ $nextAppointment->statusLabel() }}</span>
                            </div>
                            <div class="small muted">{{ $nextAppointment->office }}</div>
                            <div class="row tiny dim">
                                <span class="mono">{{ $nextAppointment->appointment_date->format('D, M d, Y') }}</span>
                                <span>&middot;</span>
                                <span>{{ $nextAppointment->time_slot }}</span>
                            </div>
                        </div>
                        <a href="{{ route('resident.appointments.index') }}" class="btn btn-sm btn-block mt-2">Manage appointments</a>
                    @else
                        <p class="small muted">No upcoming appointment. Reserve a time slot with the barangay hall, health center or SK office.</p>
                        <a href="{{ route('resident.appointments.create') }}" class="btn btn-accent btn-block">
                            <x-icon name="calendar" /> Book a time slot
                        </a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="building" /> Barangay desk</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Punong Barangay</dt><dd>{{ $barangay['captain'] }}</dd></div>
                        <div class="d"><dt>Office</dt><dd>{{ $barangay['name'] }}</dd></div>
                        <div class="d"><dt>Address</dt><dd>{{ $barangay['address'] }}</dd></div>
                        <div class="d"><dt>Hotline</dt><dd class="mono">{{ $barangay['contact'] }}</dd></div>
                    </div>
                    <hr class="divider">
                    <p class="small muted mb-0">{{ $barangay['motto'] }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .empty .ico svg { width: 26px; height: 26px; background: none; border: 0; border-radius: 0; margin: auto; }
</style>
@endpush
