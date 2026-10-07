@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', $job->title.' — '.config('app.name'))
@section('topbar-title', 'Job details')

@php($daysLeft = $job->deadline ? (int) now()->startOfDay()->diffInDays($job->deadline, false) : null)

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Livelihood board</span>
            <h1>{{ $job->title }}</h1>
            <p class="sub">
                {{ $job->company }} &middot; {{ $job->location }} &middot; Posted {{ $job->created_at->diffForHumans() }}
            </p>
        </div>
        <div class="row">
            @if ($job->is_featured)
                <span class="badge badge-amber">Featured posting</span>
            @endif
            <a href="{{ route('resident.jobs.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to board
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <span class="chip active"><x-icon name="briefcase" size="14" /> {{ $job->typeName() }}</span>
        <span class="chip"><x-icon name="layers" size="14" /> {{ $job->categoryName() }}</span>
        <span class="chip"><x-icon name="star" size="14" /> {{ $job->salary ?: 'Negotiable salary' }}</span>
        @if ($daysLeft !== null)
            <span class="chip {{ $daysLeft <= 3 ? 'active' : '' }}">
                <x-icon name="clock" size="14" />
                {{ $daysLeft > 0 ? $daysLeft.' day'.($daysLeft === 1 ? '' : 's').' left' : ($daysLeft === 0 ? 'Deadline is today' : 'Deadline passed') }}
            </span>
        @endif
    </div>

    <div class="grid grid-23">
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="file" /> The role</h2>
                </div>
                <div class="card-body">
                    <p class="muted mb-0">{!! nl2br(e($job->description)) !!}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clipboard" /> Requirements</h2>
                </div>
                <div class="card-body">
                    @if ($job->requirements)
                        <p class="muted mb-0">{!! nl2br(e($job->requirements)) !!}</p>
                    @else
                        <p class="small dim mb-0">No specific requirements listed — message the contact person for details.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="send" /> How to apply</h2>
                </div>
                <div class="card-body">
                    <div class="stack-sm">
                        <div class="queue-row"><span class="qn">1</span><span class="grow">Prepare an updated resume or work reference.</span></div>
                        <div class="queue-row"><span class="qn">2</span><span class="grow">Contact the person below and mention this barangay posting.</span></div>
                        <div class="queue-row"><span class="qn">3</span><span class="grow">Or visit the barangay livelihood desk for help with your application.</span></div>
                    </div>
                    <div class="alert alert-info mt-2 mb-0">
                        <x-icon name="info" size="16" />
                        <div>
                            <strong>Barangay-facilitated hiring.</strong>
                            <p class="small mb-0">Report any employer asking for payment in exchange for employment — officials will follow up.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="sliders" /> At a glance</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Employer</dt><dd>{{ $job->company }}</dd></div>
                        <div class="d"><dt>Work type</dt><dd>{{ $job->typeName() }}</dd></div>
                        <div class="d"><dt>Category</dt><dd>{{ $job->categoryName() }}</dd></div>
                        <div class="d"><dt>Location</dt><dd>{{ $job->location }}</dd></div>
                        <div class="d"><dt>Salary</dt><dd>{{ $job->salary ?: 'Negotiable' }}</dd></div>
                        <div class="d"><dt>Deadline</dt><dd>{{ $job->deadline?->format('M d, Y') ?? 'Open until filled' }}</dd></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="phone" /> Contact person</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Name</dt><dd>{{ $job->contact_person ?: 'Look for the HR desk' }}</dd></div>
                        @if ($job->contact_number)
                            <div class="d"><dt>Mobile</dt><dd><a href="tel:{{ preg_replace('/\s+/', '', $job->contact_number) }}" class="mono">{{ $job->contact_number }}</a></dd></div>
                        @endif
                        @if ($job->contact_email)
                            <div class="d"><dt>Email</dt><dd><a href="mailto:{{ $job->contact_email }}">{{ $job->contact_email }}</a></dd></div>
                        @endif
                    </div>

                    <div class="row mt-2">
                        @if ($job->contact_number)
                            <a href="tel:{{ preg_replace('/\s+/', '', $job->contact_number) }}" class="btn btn-success grow">
                                <x-icon name="phone-call" /> Call now
                            </a>
                        @endif
                        @if ($job->contact_email)
                            <a href="mailto:{{ $job->contact_email }}" class="btn btn-info grow">
                                <x-icon name="mail" /> Send email
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('resident.jobs.index') }}" class="btn btn-block">
                <x-icon name="briefcase" /> See all openings
            </a>
        </div>
    </div>
@endsection
