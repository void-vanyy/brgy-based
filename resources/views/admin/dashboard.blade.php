@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Dashboard — '.config('app.name'))
@section('topbar-title', 'Dashboard')

@section('content')
    @php
        $statusBadge = \App\Http\Controllers\Admin\ComplaintController::STATUS_BADGES;
        $statusLabels = \App\Http\Controllers\Admin\ComplaintController::STATUSES;
        $priorityBadge = \App\Http\Controllers\Admin\ComplaintController::PRIORITY_BADGES;
        $priorityLabels = \App\Http\Controllers\Admin\ComplaintController::PRIORITIES;
        $peakBar = $peakBar ?? collect($bars)->sortByDesc('count')->first() ?? [
            'key' => 'received',
            'label' => $statusLabels['received'] ?? 'Received',
            'count' => 0,
        ];
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Command centre</span>
            <h1>Barangay overview</h1>
            <p class="sub">Live snapshot of cases, transactions and the queue — {{ now()->format('l, F j, Y') }}.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.queue.index') }}" class="btn">
                <x-icon name="ticket" size="16" /> Queue board
            </a>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <x-icon name="plus" size="16" /> New announcement
            </a>
        </div>
    </div>

    {{-- ============ headline numbers ============ --}}
    <div class="stats mb-3">
        @foreach ($stats as $stat)
            <a href="{{ $stat['href'] }}" class="stat tone-{{ $stat['tone'] }}">
                <div class="row between items-center">
                    <span class="stat-label">{{ $stat['label'] }}</span>
                    <x-icon name="{{ $stat['icon'] }}" size="16" class="dim" />
                </div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-meta">{{ $stat['meta'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- ============ breakdown + queue ============ --}}
    <div class="grid grid-23 mb-3">
        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="chart" /> Complaints by status</h2>
                <span class="badge badge-neutral">{{ $total }} total</span>
            </div>
            <div class="card-body">
                @if ($total === 0)
                    <div class="empty">
                        <div class="ico"><x-icon name="chart" size="22" /></div>
                        <h3>Nothing charted yet</h3>
                        <p>As soon as residents file a report, the breakdown appears here.</p>
                        <a href="{{ route('admin.complaints.index') }}" class="btn btn-ghost btn-sm">Open complaints</a>
                    </div>
                @else
                    <div class="bars">
                        @foreach ($bars as $bar)
                            <div class="bar-row">
                                <span class="lbl">{{ $bar['label'] }}</span>
                                <div class="progress">
                                    <i style="width: {{ max($bar['pct'], $bar['count'] > 0 ? 6 : 0) }}%"></i>
                                </div>
                                <span class="val">{{ $bar['count'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <hr class="divider">

                    <div class="between row">
                        <span class="small muted">Heaviest stage</span>
                        <span class="badge {{ $statusBadge[$peakBar['key']] ?? 'badge-neutral' }}">
                            {{ $peakBar['label'] ?? 'No data' }} · {{ $peakBar['count'] ?? 0 }}
                        </span>
                    </div>
                @endif
            </div>
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="ticket" /> Today&rsquo;s queue</h2>
                <a href="{{ route('admin.queue.index') }}" class="btn btn-sm btn-ghost">Control board</a>
            </div>
            <div class="card-body stack">
                <div class="board">
                    <div class="now-label">Now serving</div>
                    <div class="now-num">{{ $nowServing?->ticket_no ?? '—' }}</div>
                    <div class="now-win">{{ $nowServing?->window ?? 'No ticket called yet' }}</div>
                    <div class="split">
                        <div><span>Waiting</span><b>{{ $waiting }}</b></div>
                        <div><span>Served</span><b>{{ $servedToday }}</b></div>
                        <div><span>Skipped</span><b>{{ $skippedToday }}</b></div>
                    </div>
                </div>

                <div class="stack-sm">
                    @forelse ($queueLine as $ticket)
                        <div class="queue-row">
                            <span class="qn">{{ $ticket->ticket_no }}</span>
                            <span class="grow">
                                {{ $ticket->name_on_ticket }}
                                <span class="tiny dim"> · {{ $ticket->serviceName() }}</span>
                            </span>
                            <span class="tiny dim nowrap">~{{ $ticket->waitMinutes() }} min</span>
                        </div>
                    @empty
                        <p class="small dim mb-0">Nobody is in line right now.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    {{-- ============ latest activity ============ --}}
    <div class="grid grid-23">
        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="alert" /> Latest complaints</h2>
                <a href="{{ route('admin.complaints.index') }}" class="btn btn-sm btn-ghost">View all</a>
            </div>

            @if ($latestComplaints->isEmpty())
                <div class="empty">
                    <div class="ico"><x-icon name="check-circle" size="22" /></div>
                    <h3>No complaints on record</h3>
                    <p>The barangay is at peace — new reports will land here.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Complaint</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestComplaints as $complaint)
                                <tr>
                                    <td class="mono tiny nowrap">{{ $complaint->reference_no }}</td>
                                    <td class="strong">
                                        {{ $complaint->title }}
                                        <div class="tiny dim">{{ $complaint->resident?->name ?? 'Resident' }} · {{ $complaint->purok ?: 'No purok' }}</div>
                                    </td>
                                    <td><span class="badge {{ $priorityBadge[$complaint->priority] ?? 'badge-neutral' }}">{{ $priorityLabels[$complaint->priority] ?? $complaint->priority }}</span></td>
                                    <td><span class="badge {{ $statusBadge[$complaint->status] ?? 'badge-neutral' }}">{{ $statusLabels[$complaint->status] ?? $complaint->status }}</span></td>
                                    <td class="right nowrap">
                                        <a href="{{ route('admin.complaints.show', $complaint) }}" class="btn btn-sm btn-ghost">Open</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="card-title"><x-icon name="megaphone" /> Latest announcements</h2>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-ghost">Manage</a>
            </div>

            @if ($announcements->isEmpty())
                <div class="empty">
                    <div class="ico"><x-icon name="megaphone" size="22" /></div>
                    <h3>Nothing published</h3>
                    <p>Bulletins you publish are shown to residents here.</p>
                    <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary btn-sm">Write one</a>
                </div>
            @else
                <div class="list">
                    @foreach ($announcements as $announcement)
                        <div class="list-item">
                            <span class="avatar sm {{ $announcement->is_pinned ? 'amber' : 'neutral' }}">
                                <x-icon name="{{ $announcement->is_pinned ? 'pin' : 'megaphone' }}" size="14" />
                            </span>
                            <div class="grow">
                                <div class="bold small">{{ $announcement->title }}</div>
                                <div class="tiny dim">
                                    {{ \Illuminate\Support\Str::limit($announcement->body, 90) }}
                                </div>
                                <div class="row gap-1 mt-1">
                                    <span class="badge badge-green">Published</span>
                                    @if ($announcement->event_date)
                                        <span class="tiny dim"><x-icon name="calendar" size="12" /> {{ $announcement->event_date }}</span>
                                    @endif
                                    <span class="tiny dim">{{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.announcements.preview', $announcement) }}" class="btn btn-sm btn-icon btn-ghost" title="Preview">
                                <x-icon name="eye" size="15" />
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
