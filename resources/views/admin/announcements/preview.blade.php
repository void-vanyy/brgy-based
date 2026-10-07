@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Preview — '.config('app.name'))
@section('topbar-title', 'Announcement preview')

@section('content')
    @php($categories = \App\Http\Controllers\Admin\AnnouncementController::CATEGORIES)

    <div class="page-head">
        <div>
            <span class="eyebrow">Preview</span>
            <h1>How residents will see it</h1>
            <p class="sub">Read-only render of the announcement card and full bulletin.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-info">
                <x-icon name="edit" size="15" /> Edit
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-ghost">Back to list</a>
        </div>
    </div>

    <div class="grid grid-23">
        <div class="stack">
            <div class="alert {{ $announcement->is_published ? 'alert-success' : 'alert-warning' }}">
                <x-icon name="{{ $announcement->is_published ? 'check-circle' : 'info' }}" size="17" />
                <div>
                    <strong>{{ $announcement->is_published ? 'Live on the portal' : 'Draft — not visible to residents' }}</strong>
                    <p class="mb-0">
                        {{ $announcement->is_published
                            ? 'Published '.($announcement->published_at?->format('M j, Y g:i A') ?? $announcement->created_at->format('M j, Y g:i A'))
                            : 'Tick “Publish to the resident portal” on the edit screen to push this live.' }}
                    </p>
                </div>
            </div>

            <article class="announce-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                <div class="row between items-center mb-2">
                    <div class="row gap-1">
                        <span class="badge badge-blue">{{ $categories[$announcement->category] ?? $announcement->category }}</span>
                        @if ($announcement->is_pinned)
                            <span class="pin-note"><x-icon name="pin" size="13" /> Pinned</span>
                        @endif
                    </div>
                    <span class="tiny dim">{{ ($announcement->published_at ?? $announcement->created_at)->format('F j, Y') }}</span>
                </div>

                <h1 style="font-size:1.45rem">{{ $announcement->title }}</h1>

                <div class="row gap-1 tiny dim mb-2">
                    @if ($announcement->event_date)
                        <span class="row" style="gap:.3rem"><x-icon name="calendar" size="13" /> {{ $announcement->event_date }}</span>
                    @endif
                    @if ($announcement->location)
                        <span class="row" style="gap:.3rem"><x-icon name="pin" size="13" /> {{ $announcement->location }}</span>
                    @endif
                    <span class="row" style="gap:.3rem"><x-icon name="user" size="13" /> {{ $announcement->creator?->name ?? 'Barangay office' }}</span>
                </div>

                <hr class="divider">

                <div class="body" style="font-size:.98rem; color:var(--text)">{{ $announcement->body }}</div>
            </article>
        </div>

        <div class="stack">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clipboard" /> Card preview</h2>
                </div>
                <div class="card-body">
                    <p class="tiny dim">This is the shortened card shown on dashboards and the home page.</p>
                    <article class="announce-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                        <div class="row mb-1">
                            <span class="badge badge-blue">{{ $categories[$announcement->category] ?? $announcement->category }}</span>
                        </div>
                        <h3>{{ \Illuminate\Support\Str::limit($announcement->title, 60) }}</h3>
                        <div class="body">{{ \Illuminate\Support\Str::limit($announcement->body, 150) }}</div>
                    </article>
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> Record</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Category</dt><dd>{{ $categories[$announcement->category] ?? $announcement->category }}</dd></div>
                        <div class="d"><dt>Event date</dt><dd>{{ $announcement->event_date ?: '—' }}</dd></div>
                        <div class="d"><dt>Location</dt><dd>{{ $announcement->location ?: '—' }}</dd></div>
                        <div class="d"><dt>Pinned</dt><dd>{{ $announcement->is_pinned ? 'Yes' : 'No' }}</dd></div>
                        <div class="d"><dt>State</dt><dd>{{ $announcement->is_published ? 'Published' : 'Draft' }}</dd></div>
                        <div class="d"><dt>Created</dt><dd>{{ $announcement->created_at->format('M j, Y g:i A') }}</dd></div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
