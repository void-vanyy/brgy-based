@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Document requests — '.config('app.name'))
@section('topbar-title', 'Document requests')

@section('content')
    @php
        $statusBadge = [
            'pending' => 'badge-cyan',
            'under_review' => 'badge-amber',
            'approved' => 'badge-green',
            'ready_for_release' => 'badge-amber',
            'released' => 'badge-green',
            'rejected' => 'badge-rose',
        ];
        $currentStatus = request('status');
        $currentPage = $requests->currentPage();
        $lastPage = $requests->lastPage();
        $windowStart = max(1, $currentPage - 2);
        $windowEnd = min($lastPage, $currentPage + 2);
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Records desk</span>
            <h1>Document requests</h1>
            <p class="sub">Certificates, clearances and IDs queued for processing and release.</p>
        </div>
        <div class="row">
            <span class="badge badge-cyan">{{ ($counts['pending'] ?? 0) + ($counts['under_review'] ?? 0) }} in the queue</span>
        </div>
    </div>

    {{-- ============ status tabs ============ --}}
    <div class="tabs">
        <a href="{{ route('admin.requests.index') }}" class="tab {{ ! in_array($currentStatus, array_keys($statuses), true) ? 'active' : '' }}">
            All <span class="count">{{ $requests->total() }}</span>
        </a>
        @foreach ($statuses as $key => $label)
            <a href="{{ route('admin.requests.index', ['status' => $key]) }}" class="tab {{ $currentStatus === $key ? 'active' : '' }}">
                {{ $label }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    {{-- ============ filters ============ --}}
    <form method="GET" action="{{ route('admin.requests.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search reference number or resident…"
                    value="{{ old('q', request('q')) }}">
            </div>

            <select name="status" class="select" style="width:auto; min-width:175px">
                <option value="">All statuses</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($currentStatus === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Apply</button>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost">Reset</a>
            <span class="small dim grow right">{{ $requests->total() }} request(s)</span>
        </div>
    </form>

    {{-- ============ results ============ --}}
    <section class="card">
        @if ($requests->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="file" size="22" /></div>
                <h3>No requests found</h3>
                <p>Nothing matches this filter. Residents&rsquo; submissions will appear here the moment they file one.</p>
                <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost btn-sm">Clear filters</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Document</th>
                            <th>Requester</th>
                            <th>Copies / fee</th>
                            <th>Status</th>
                            <th>Filed</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $doc)
                            <tr>
                                <td class="mono tiny nowrap">{{ $doc->reference_no }}</td>
                                <td class="strong">
                                    <a href="{{ route('admin.requests.show', $doc) }}">{{ $doc->typeName() }}</a>
                                    <div class="tiny dim">{{ \Illuminate\Support\Str::limit($doc->purpose, 70) }}</div>
                                </td>
                                <td class="nowrap">
                                    {{ $doc->resident?->name ?? 'Resident' }}
                                    <div class="tiny dim">{{ $doc->resident?->purok ?: '—' }}</div>
                                </td>
                                <td class="nowrap">
                                    {{ $doc->copies }} ×
                                    <div class="tiny dim">₱ {{ number_format((float) $doc->fee, 2) }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge[$doc->status] ?? 'badge-neutral' }}">{{ $doc->statusLabel() }}</span>
                                    @if ($doc->released_at)
                                        <div class="tiny dim">released {{ $doc->released_at->format('M j') }}</div>
                                    @endif
                                </td>
                                <td class="nowrap tiny dim">{{ $doc->created_at->format('M j, Y') }}</td>
                                <td class="right nowrap">
                                    <a href="{{ route('admin.requests.show', $doc) }}" class="btn btn-sm btn-ghost">Process</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="card-foot">
                    <nav class="pagination mt-0">
                        @if ($requests->onFirstPage())
                            <span class="dim">&lsaquo;</span>
                        @else
                            <a href="{{ $requests->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @if ($windowStart > 1)
                            <a href="{{ $requests->url(1) }}">1</a>
                            @if ($windowStart > 2)<span class="dim">…</span>@endif
                        @endif

                        @for ($i = $windowStart; $i <= $windowEnd; $i++)
                            @if ($i === $currentPage)
                                <span class="active">{{ $i }}</span>
                            @else
                                <a href="{{ $requests->url($i) }}">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($windowEnd < $lastPage)
                            @if ($windowEnd < $lastPage - 1)<span class="dim">…</span>@endif
                            <a href="{{ $requests->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        @if ($requests->hasMorePages())
                            <a href="{{ $requests->nextPageUrl() }}">&rsaquo;</a>
                        @else
                            <span class="dim">&rsaquo;</span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>
@endsection
