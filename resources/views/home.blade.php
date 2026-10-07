@extends('layouts.guest')

@section('title', ($settings['barangay_name'] ?? config('app.name')).' — Digital front desk for barangay services')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
<style>
    .section { position: relative; z-index: 1; }
    .section.tight { padding-top: 0; }

    .hero-grid { display: grid; grid-template-columns: 1.02fr .98fr; gap: clamp(1.6rem, 4vw, 3rem); align-items: center; }

    .svc { display: block; color: inherit; text-decoration: none; }
    .svc h3 { color: var(--text); transition: color .18s ease; }
    .svc:hover h3 { color: var(--primary); }
    .svc .more {
        display: flex; align-items: center; gap: .4rem;
        margin-top: 1.05rem;
        font-family: var(--mono); font-size: .72rem; font-weight: 650;
        letter-spacing: .12em; text-transform: uppercase;
        color: var(--primary);
    }

    .step-num {
        font-family: var(--mono);
        font-size: 1.9rem; font-weight: 800; line-height: 1;
        color: var(--primary); opacity: .85;
        margin-bottom: .75rem;
    }

    a.a-plain { color: inherit; }
    a.a-plain:hover { color: var(--primary); }

    .head-row {
        display: flex; align-items: flex-end; justify-content: space-between;
        gap: 1.2rem; flex-wrap: wrap; margin-bottom: 2.2rem;
    }
    .head-row .section-head { margin-bottom: 0; }

    .meta-row {
        display: flex; align-items: center; gap: .8rem; flex-wrap: wrap;
        font-size: .76rem; color: var(--dim); margin: .15rem 0 .75rem;
    }
    .meta-row span { display: inline-flex; align-items: center; gap: .34rem; }
    .job-card .meta span { display: inline-flex; align-items: center; gap: .34rem; }

    .cta-band { padding: clamp(2.2rem, 5vw, 3.4rem) clamp(1.2rem, 4vw, 2.6rem); text-align: center; }
    .cta-band h2 { font-size: clamp(1.5rem, 3vw, 2.1rem); letter-spacing: -.03em; }
    .cta-band p { max-width: 56ch; margin-left: auto; margin-right: auto; }
    .cta-row { justify-content: center; margin-top: 1.5rem; }

    .map-caption { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-top: .8rem; }

    @media (max-width: 1080px) {
        .hero-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $barangay = $settings['barangay_name'] ?? config('app.name');
    $address  = $settings['barangay_address'] ?? '';
    $contact  = $settings['barangay_contact'] ?? '';
    $captain  = $settings['barangay_capain'] ?? '';
    $motto    = $settings['barangay_motto'] ?? '';

    $services = [
        ['icon' => 'alert',     'title' => 'File a complaint &amp; track it', 'route' => route('resident.complaints.create'), 'text' => 'Describe the concern, drop a pin and receive a reference number. Every status change shows up in your tracker.'],
        ['icon' => 'file',      'title' => 'Certificates &amp; clearances',   'route' => route('resident.documents.create'), 'text' => 'Barangay clearance, residency, indigency and more — request online, pay the fee at the counter when it is ready.'],
        ['icon' => 'calendar',  'title' => 'Appointments',                    'route' => route('resident.appointments.create'), 'text' => 'Book a slot with the captain, the secretary or the health centre so nobody waits the whole morning.'],
        ['icon' => 'ticket',    'title' => 'Queueing system',                 'route' => route('resident.queue.index'), 'text' => 'Take a number from home, watch the live board and arrive only when your window is two people away.'],
        ['icon' => 'chat',      'title' => 'Freedom wall',                    'route' => route('resident.freedom-wall.index'), 'text' => 'Shout-outs, concerns and suggestions for the barangay — post anonymously and let the community react.'],
        ['icon' => 'box',       'title' => 'Lost &amp; found',                'route' => route('resident.lost-found.index'), 'text' => 'Report a lost item or post something you found. Claims are verified at the barangay hall.'],
        ['icon' => 'briefcase', 'title' => 'Livelihood &amp; jobs',           'route' => route('resident.jobs.index'), 'text' => 'Curated openings from nearby employers and barangay projects, with deadlines and contact details.'],
        ['icon' => 'map',       'title' => 'Barangay map',                    'route' => route('resident.map.index'), 'text' => 'Landmarks, health facilities and reported concerns on one interactive map of the barangay.'],
    ];

    $steps = [
        ['no' => '01', 'title' => 'Register',   'text' => 'One account with your purok and address — verified at the barangay hall.'],
        ['no' => '02', 'title' => 'Request',    'text' => 'Pick a service, fill a short form and submit it in under five minutes.'],
        ['no' => '03', 'title' => 'Track',      'text' => 'Follow every update on your reference number, from received to resolved.'],
        ['no' => '04', 'title' => 'Claim',      'text' => 'Get notified when the document is ready, then claim it at your window.'],
    ];

    $categoryTone = [
        'information' => 'badge-blue',
        'event'       => 'badge-violet',
        'emergency'   => 'badge-rose',
        'program'     => 'badge-cyan',
        'health'      => 'badge-green',
        'ordinance'   => 'badge-amber',
    ];
@endphp

{{-- ============================= HERO ============================= --}}
<section class="hero">
    <div class="hero-inner">
        <div class="hero-grid">
            <div>
                <span class="eyebrow">Barangay digital front desk</span>

                <h1>
                    Every barangay transaction,
                    <span class="gradient-text">without the queue.</span>
                </h1>

                <p class="lead">
                    Complaints, certificates, appointments, queue numbers, announcements and livelihood
                    listings for {{ $barangay }} — filed online, tracked in real time, claimed at the counter
                    only when it is ready.
                </p>

                <div class="cta">
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                        Create an account <x-icon name="chevron" size="16" />
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">
                        <x-icon name="login" size="16" /> Sign in
                    </a>
                    <a href="{{ route('features') }}" class="btn btn-ghost btn-lg">
                        Explore the services
                    </a>
                </div>

                <div class="marquee">
                    <span class="chip"><x-icon name="alert" size="14" /> Complaint tracker</span>
                    <span class="chip"><x-icon name="file" size="14" /> Certificates</span>
                    <span class="chip"><x-icon name="ticket" size="14" /> Live queue</span>
                    <span class="chip"><x-icon name="megaphone" size="14" /> Announcements</span>
                    <span class="chip"><x-icon name="briefcase" size="14" /> Jobs</span>
                    <span class="chip"><x-icon name="map" size="14" /> Barangay map</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="row between" style="margin-bottom:.9rem">
                    <span class="eyebrow">Live at the barangay hall</span>
                    <span class="badge badge-green">Open now</span>
                </div>

                <div class="board">
                    <div class="now-label">Now serving</div>
                    <div class="now-num" id="hero-now">A-006</div>
                    <div class="now-win" id="hero-window">Window 1 &middot; Document Request</div>
                    <div class="split">
                        <div><span>Waiting</span><b id="hero-waiting">6</b></div>
                        <div><span>Called</span><b id="hero-called">1</b></div>
                        <div><span>Served</span><b id="hero-done">4</b></div>
                    </div>
                </div>

                <div class="stack-sm" style="margin-top:.9rem">
                    <div class="queue-row">
                        <span class="qn">A-007</span>
                        <span class="grow">Payment &amp; Fees</span>
                        <span class="badge badge-cyan">Waiting</span>
                    </div>
                    <div class="queue-row">
                        <span class="qn">A-008</span>
                        <span class="grow">General Inquiry</span>
                        <span class="badge badge-cyan">Waiting</span>
                    </div>
                </div>

                <p class="small dim center" style="margin:.9rem 0 0">
                    Live board powered by <span class="mono">/api/queue/status</span> &middot; refreshes automatically
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ============================= STATS ============================= --}}
<section class="section tight">
    <div class="section-inner">
        <div class="stats">
            <div class="stat tone-cyan">
                <div class="stat-label">Availability</div>
                <div class="stat-value">24/7</div>
                <div class="stat-meta">Online requests, any hour of the day</div>
            </div>
            <div class="stat tone-violet">
                <div class="stat-label">Speed</div>
                <div class="stat-value">&lt; 5 min</div>
                <div class="stat-meta">Average time to file a complaint</div>
            </div>
            <div class="stat tone-green">
                <div class="stat-label">Coverage</div>
                <div class="stat-value">7</div>
                <div class="stat-meta">Puroks served across the barangay</div>
            </div>
            <div class="stat tone-amber">
                <div class="stat-label">Portal</div>
                <div class="stat-value">1</div>
                <div class="stat-meta">One account for every transaction</div>
            </div>
        </div>
    </div>
</section>

{{-- ============================= SERVICES ============================= --}}
<section class="section">
    <div class="section-inner">
        <div class="section-head">
            <span class="eyebrow">What you can do here</span>
            <h2>Eight modules, <span class="gradient-text">one resident account.</span></h2>
            <p>
                Everything the barangay office used to do with paper forms now runs on the same
                track — and every module shows you exactly where your request stands.
            </p>
        </div>

        <div class="grid grid-2">
            @foreach ($services as $service)
                <a class="feature-card svc" href="{{ $service['route'] }}">
                    <span class="ficon"><x-icon name="{{ $service['icon'] }}" size="20" /></span>
                    <h3>{!! $service['title'] !!}</h3>
                    <p>{!! $service['text'] !!}</p>
                    <span class="more">Open module <x-icon name="chevron" size="13" /></span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================= HOW IT WORKS ============================= --}}
<section class="section" style="padding-top:0">
    <div class="section-inner">
        <div class="section-head">
            <span class="eyebrow">How it works</span>
            <h2>Four steps, start to claim.</h2>
            <p>No queues to wake up for, no photocopied forms to lose — just a reference number you can follow.</p>
        </div>

        <div class="grid grid-4">
            @foreach ($steps as $step)
                <div class="card pad">
                    <div class="step-num">{{ $step['no'] }}</div>
                    <h3>{{ $step['title'] }}</h3>
                    <p class="small muted" style="margin:0">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================= ANNOUNCEMENTS ============================= --}}
<section class="section" id="announcements">
    <div class="section-inner">
        <div class="head-row">
            <div class="section-head">
                <span class="eyebrow">Barangay bulletin</span>
                <h2>Announcements you should not miss.</h2>
                <p>Schedules, advisories and ordinances — pinned notices stay on top until they expire.</p>
            </div>

            <a href="{{ route('resident.announcements.index') }}" class="btn btn-ghost">
                All announcements <x-icon name="chevron" size="15" />
            </a>
        </div>

        <div class="grid grid-2">
            @forelse ($announcements as $announcement)
                <article class="announce-card {{ $announcement->is_pinned ? 'pinned' : '' }}">
                    <div class="row between">
                        <span class="badge {{ $categoryTone[$announcement->category] ?? 'badge-neutral' }}">
                            {{ $announcement->category }}
                        </span>
                        @if ($announcement->is_pinned)
                            <span class="pin-note"><x-icon name="pin" size="12" /> Pinned</span>
                        @endif
                    </div>

                    <h3>
                        <a class="a-plain" href="{{ route('resident.announcements.show', $announcement) }}">
                            {{ $announcement->title }}
                        </a>
                    </h3>

                    <div class="meta-row">
                        @if ($announcement->event_date)
                            <span><x-icon name="calendar" size="13" /> {{ date('M d, Y', strtotime($announcement->event_date)) }}</span>
                        @endif
                        <span><x-icon name="clock" size="13" /> {{ optional($announcement->published_at)->diffForHumans() ?? 'Draft' }}</span>
                        @if ($announcement->location)
                            <span><x-icon name="pin" size="13" /> {{ $announcement->location }}</span>
                        @endif
                    </div>

                    <p class="body">{{ \Illuminate\Support\Str::limit($announcement->body, 170) }}</p>
                </article>
            @empty
                <div class="card pad">
                    <p class="muted" style="margin:0">No published announcements yet — check back soon.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================= LIVELIHOOD ============================= --}}
<section class="section" id="livelihood" style="padding-top:0">
    <div class="section-inner">
        <div class="head-row">
            <div class="section-head">
                <span class="eyebrow">Livelihood board</span>
                <h2>Work within reach of home.</h2>
                <p>Openings posted by nearby employers and barangay projects, verified by the barangay staff.</p>
            </div>

            <a href="{{ route('resident.jobs.index') }}" class="btn btn-ghost">
                Browse all listings <x-icon name="chevron" size="15" />
            </a>
        </div>

        <div class="grid grid-3">
            @forelse ($jobs as $job)
                <article class="job-card">
                    <div class="row between">
                        <span class="badge badge-cyan">{{ $job->typeName() }}</span>
                        @if ($job->is_featured)
                            <span class="badge badge-violet">Featured</span>
                        @endif
                    </div>

                    <h3>{{ $job->title }}</h3>
                    <div class="co">{{ $job->company }}</div>

                    <div class="meta">
                        <span><x-icon name="pin" size="12" /> {{ $job->location }}</span>
                        <span><x-icon name="layers" size="12" /> {{ $job->categoryName() }}</span>
                        @if ($job->deadline)
                            <span><x-icon name="clock" size="12" /> Until {{ optional($job->deadline)->format('M d, Y') }}</span>
                        @endif
                    </div>

                    <div class="bold">{{ $job->salary }}</div>

                    <a href="{{ route('resident.jobs.show', $job) }}" class="btn btn-ghost btn-sm" style="margin-top:.3rem">
                        View listing <x-icon name="chevron" size="14" />
                    </a>
                </article>
            @empty
                <div class="card pad">
                    <p class="muted" style="margin:0">No open listings right now — new jobs are posted weekly.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================= MAP + LOST & FOUND ============================= --}}
<section class="section">
    <div class="section-inner">
        <div class="section-head">
            <span class="eyebrow">Know your barangay</span>
            <h2>Landmarks and reported concerns, <span class="gradient-text">on one map.</span></h2>
            <p>
                Offices, schools, health facilities and every complaint with a pinned location —
                plotted live for residents and barangay staff.
            </p>
        </div>

        <div class="grid grid-23">
            <div>
                <div id="home-map" class="map-wrap short"></div>

                <div class="map-legend">
                    <span><i style="background:#8b5cf6"></i> Barangay landmark</span>
                    <span><i style="background:#fb7185"></i> Urgent / high concern</span>
                    <span><i style="background:#60a5fa"></i> Routine concern</span>
                </div>

                <div class="map-caption">
                    <span class="small muted">
                        {{ $barangay }} &middot; {{ $address }}
                        @if ($contact) &middot; {{ $contact }} @endif
                    </span>
                    <span class="mono tiny dim">center 14.6042, 121.0410 &middot; zoom 16</span>
                </div>
            </div>

            <div class="stack">
                <div class="card">
                    <div class="card-head">
                        <h3 class="card-title"><x-icon name="box" size="17" /> Fresh lost &amp; found reports</h3>
                    </div>

                    <div class="list">
                        @forelse ($lostFound as $item)
                            <div class="list-item">
                                <span class="avatar neutral sm">
                                    <x-icon name="search" size="15" />
                                </span>
                                <div class="grow" style="min-width:0">
                                    <div class="row between" style="gap:.5rem">
                                        <strong class="small" style="color:var(--text)">{{ $item->item_name }}</strong>
                                        <span class="badge {{ $item->status === 'found' ? 'badge-green' : ($item->status === 'claimed' ? 'badge-blue' : 'badge-rose') }}">
                                            {{ $item->statusLabel() }}
                                        </span>
                                    </div>
                                    <div class="tiny dim" style="margin-top:.25rem">
                                        {{ $item->location }} &middot; {{ optional($item->date_occurred)->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="list-item">
                                <span class="small muted">No lost or found items posted yet.</span>
                            </div>
                        @endforelse
                    </div>

                    <div class="card-foot">
                        <a href="{{ route('resident.lost-found.index') }}" class="small">
                            Browse the lost &amp; found board <x-icon name="chevron" size="13" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================= CTA BAND ============================= --}}
<section class="section" style="padding-top:0">
    <div class="section-inner">
        <div class="card cta-band">
            <span class="eyebrow">Ready when you are</span>
            <h2 style="margin-top:.7rem">Your next transaction can start right now.</h2>
            <p class="muted">
                Create a resident account to file requests, or sign in to pick up where you left off.
                Barangay officials use the same login for the officials console.
            </p>

            <div class="row cta-row">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                    Create an account <x-icon name="chevron" size="16" />
                </a>
                <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">
                    <x-icon name="login" size="16" /> Sign in
                </a>
            </div>

            @if ($motto)
                <p class="mono small dim" style="margin:1.4rem 0 0">{{ $motto }}</p>
            @endif
            @if ($captain)
                <p class="tiny dim" style="margin:.35rem 0 0">Punong Barangay: {{ $captain }}</p>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script src="{{ asset('vendor/leaflet/leaflet.js') }}" defer></script>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ---------- live queue board in the hero ---------- */
    var services = {
        document_request: 'Document Request',
        complaint: 'Complaint / Concern',
        payment: 'Payment & Fees',
        consultation: 'Legal Consultation',
        general: 'General Inquiry'
    };

    function renderQueue(data) {
        if (!data) return;
        var now = document.getElementById('hero-now');
        var win = document.getElementById('hero-window');
        var list = data.tickets || [];
        var serving = data.now_serving ? list.filter(function (t) {
            return t.ticket_no === data.now_serving;
        })[0] : null;

        if (now) now.textContent = data.now_serving || '—';
        if (win) {
            win.textContent = data.now_serving
                ? (data.window || 'Window') + ' · ' + (services[serving ? serving.service : ''] || 'Now serving')
                : 'Waiting for the next call';
        }
        if (document.getElementById('hero-waiting')) document.getElementById('hero-waiting').textContent = data.waiting_count;
        if (document.getElementById('hero-called')) document.getElementById('hero-called').textContent = data.called_count;
        if (document.getElementById('hero-done')) document.getElementById('hero-done').textContent = data.done_count;
    }

    if (window.pollUrl) {
        window.pollUrl('{{ url('/api/queue/status') }}', renderQueue, 8000);
    }

    /* ---------- map ---------- */
    var mapEl = document.getElementById('home-map');
    if (!mapEl || typeof L === 'undefined') return;

    function esc(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
            });
    }

    var barangayName = {!! json_encode($barangay) !!};
    var barangayAddress = {!! json_encode($address) !!};
    var CENTER = [14.6042, 121.0410];
    var map = L.map(mapEl, { scrollWheelZoom: false }).setView(CENTER, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.circleMarker(CENTER, {
        radius: 9, color: '#04121a', weight: 2, fillColor: '#22d3ee', fillOpacity: 1
    }).addTo(map).bindPopup('<strong>' + esc(barangayName) + '</strong><br>' + esc(barangayAddress));

    function fetchJson(url) {
        return fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : []; })
            .catch(function () { return []; });
    }

    fetchJson('{{ url('/api/map/points') }}').then(function (points) {
        (points || []).forEach(function (p) {
            L.circleMarker([p.latitude, p.longitude], {
                radius: 7, color: '#04121a', weight: 2, fillColor: '#8b5cf6', fillOpacity: .95
            }).addTo(map).bindPopup('<strong>' + esc(p.label) + '</strong>');
        });
    });

    fetchJson('{{ url('/api/map/complaints') }}').then(function (items) {
        (items || []).forEach(function (c) {
            var color = (c.priority === 'urgent' || c.priority === 'high') ? '#fb7185' : '#60a5fa';
            L.circleMarker([c.latitude, c.longitude], {
                radius: 6, color: '#04121a', weight: 2, fillColor: color, fillOpacity: .95
            }).addTo(map).bindPopup(
                '<strong>' + esc(c.title) + '</strong><br>' +
                esc(c.reference_no) + ' &middot; ' + esc(c.location) + '<br>' +
                '<span style="color:#93a1bd">' + esc(c.priority) + ' &middot; ' + esc(c.status) + '</span>'
            );
        });
    });
});
</script>
@endsection
