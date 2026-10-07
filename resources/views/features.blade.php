@extends('layouts.guest')

@section('title', 'Services — '.($settings['barangay_name'] ?? config('app.name')).' e-Services')

@push('styles')
<style>
    .section { position: relative; z-index: 1; }

    .feat-list { list-style: none; margin: .9rem 0 1.1rem; padding: 0; display: flex; flex-direction: column; gap: .5rem; }
    .feat-list li { display: flex; align-items: flex-start; gap: .55rem; font-size: .855rem; color: var(--muted); }
    .feat-list li .ico { color: var(--success); flex-shrink: 0; margin-top: .28rem; }

    .feature-card { display: flex; flex-direction: column; height: 100%; }
    .feature-card .btn { margin-top: auto; align-self: flex-start; }

    .who-row { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .95rem; }

    .table-wrap.card { border: 1px solid var(--border); }
    .yes { color: var(--success); }
    .no  { color: var(--dim); }

    .faq { display: flex; flex-direction: column; gap: .7rem; max-width: 820px; }
    .faq details {
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: linear-gradient(160deg, var(--panel-2), var(--panel));
        padding: 0 1.15rem;
    }
    .faq summary {
        cursor: pointer;
        list-style: none;
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        padding: .95rem 0;
        font-size: .93rem; font-weight: 600; color: var(--text);
    }
    .faq summary::-webkit-details-marker { display: none; }
    .faq summary .ico { flex-shrink: 0; color: var(--primary); transition: transform .18s ease; }
    .faq details[open] summary .ico { transform: rotate(180deg); }
    .faq details[open] summary { color: var(--primary); }
    .faq .answer { color: var(--muted); font-size: .87rem; padding: 0 0 1.05rem; margin: 0; }

    .compare td.center, .compare th.center { text-align: center; }
</style>
@endpush

@section('content')
@php
    $barangay = $settings['barangay_name'] ?? config('app.name');

    $features = [
        [
            'icon'   => 'alert',
            'title'  => 'Complaints &amp; case tracking',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.complaints.create'),
            'action' => 'File a complaint',
            'text'   => 'Report a concern with a location pin and follow it all the way to resolution.',
            'bullets' => [
                'Describe the issue, choose a priority and drop a map pin.',
                'Receive a reference number (CMP-0001…) the moment you submit.',
                'Watch the timeline move: received → in progress → resolved.',
                'Officials assign, remark on and close cases from their console.',
            ],
        ],
        [
            'icon'   => 'file',
            'title'  => 'Certificates &amp; clearances',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.documents.create'),
            'action' => 'Request a certificate',
            'text'   => 'Ten document types with transparent fees and a status you never have to guess about.',
            'bullets' => [
                'Barangay clearance, residency, indigency, good moral and more.',
                'See the fee before you submit — ₱50 for clearances, ₱30 for certificates.',
                'Track pending → under review → approved → ready for release.',
                'Staff approve, release and attach remarks to every request.',
            ],
        ],
        [
            'icon'   => 'calendar',
            'title'  => 'Appointments',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.appointments.create'),
            'action' => 'Book a slot',
            'text'   => 'Reserve a time with an office instead of spending a morning on a bench.',
            'bullets' => [
                'Choose the office, the date and one of six daily time slots.',
                'Cancel or reschedule before the slot without a phone call.',
                'Officials confirm, complete or mark a visit as a no-show.',
                'Reminders and remarks stay attached to the appointment record.',
            ],
        ],
        [
            'icon'   => 'ticket',
            'title'  => 'Queueing system',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.queue.index'),
            'action' => 'Take a number',
            'text'   => 'A digital queue ticket that tells you exactly when to leave the house.',
            'bullets' => [
                'Draw a ticket online and get a number like A-007.',
                'See your position in line and the estimated wait in minutes.',
                'A live lobby board shows the number and window now serving.',
                'Staff call the next resident with a single click.',
            ],
        ],
        [
            'icon'   => 'chat',
            'title'  => 'Freedom wall',
            'who'    => ['For residents'],
            'route'  => route('resident.freedom-wall.index'),
            'action' => 'Open the wall',
            'text'   => 'A moderated community board where residents can speak up without holding back.',
            'bullets' => [
                'Post anonymously — the author is never exposed to other residents.',
                'Four topics: shout-out, concern, suggestion and praise.',
                'React to posts so the barangay can gauge community sentiment.',
                'Posts stay tied to an account for responsible moderation.',
            ],
        ],
        [
            'icon'   => 'box',
            'title'  => 'Lost &amp; found',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.lost-found.index'),
            'action' => 'Browse the board',
            'text'   => 'One shared board for the wallet someone lost and the phone somebody turned in.',
            'bullets' => [
                'Post a lost or found item with the place and date it happened.',
                'Filter by category — gadgets, IDs, jewelry, pets and more.',
                'Statuses move from lost / found to claimed at the counter.',
                'Claimants verify details with barangay staff before release.',
            ],
        ],
        [
            'icon'   => 'briefcase',
            'title'  => 'Livelihood &amp; jobs',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.jobs.index'),
            'action' => 'See open jobs',
            'text'   => 'Local openings with real salaries and deadlines, curated by the barangay.',
            'bullets' => [
                'Browse open listings with salary ranges and application deadlines.',
                'Filter by category and employment type — full time, part time, contract.',
                'Featured listings from partners stay pinned on top.',
                'Officials post, edit and close listings on behalf of employers.',
            ],
        ],
        [
            'icon'   => 'map',
            'title'  => 'Barangay map',
            'who'    => ['For residents', 'For officials'],
            'route'  => route('resident.map.index'),
            'action' => 'Open the map',
            'text'   => 'The barangay at a glance: offices, schools, health facilities and live concern markers.',
            'bullets' => [
                'Landmarks: barangay hall, health centre, police post, market, school.',
                'Every complaint with coordinates appears as a colour-coded marker.',
                'Urgent and high-priority concerns stand out instantly.',
                'Served by the public JSON endpoints under /api/map.',
            ],
        ],
    ];

    $compare = [
        ['File a complaint with a map pin',     'yes', 'no'],
        ['Track a complaint timeline',          'yes', 'yes'],
        ['Assign, remark on and resolve cases', 'no',  'yes'],
        ['Request certificates &amp; clearances', 'yes', 'no'],
        ['Approve, release and stamp documents', 'no', 'yes'],
        ['Book an appointment',                 'yes', 'no'],
        ['Confirm, cancel or mark no-shows',    'no',  'yes'],
        ['Draw a queue ticket',                 'yes', 'no'],
        ['Call the next resident from the board', 'no', 'yes'],
        ['Post on the freedom wall',            'yes', 'moderate'],
        ['Read announcements &amp; advisories', 'yes', 'publish'],
        ['Post lost &amp; found items',         'yes', 'yes'],
        ['Manage resident accounts &amp; settings', 'no', 'yes'],
        ['View the barangay map',               'yes', 'yes'],
    ];

    $faq = [
        [
            'q' => 'Do I need an account for every transaction?',
            'a' => 'Yes — one resident account covers all eight modules. Registration asks for your name, purok, address and birth date so the barangay can verify you once, at the counter, and every request after that becomes a two-minute job.',
        ],
        [
            'q' => 'How much do certificates cost?',
            'a' => 'Barangay clearance and business permit endorsements carry a ₱50 fee; the other certificates cost ₱30. The exact fee is shown on the request form before you submit, and payment happens at the barangay hall counter when you claim the document.',
        ],
        [
            'q' => 'How long does a complaint take?',
            'a' => 'Most concerns are acknowledged the same day. Simple sanitation and noise cases are typically resolved within two to three working days; cases that need the city government — like road works — are endorsed and marked on hold so you can still see where they are.',
        ],
        [
            'q' => 'Is the freedom wall really anonymous?',
            'a' => 'Posts are stored as anonymous by default and other residents only ever see “Anonymous Resident”. The account behind the post is kept for moderation purposes, so threats, hate speech and personal attacks can still be acted on by barangay officials.',
        ],
        [
            'q' => 'What if I cannot go to the barangay hall during office hours?',
            'a' => 'Requests can be filed any hour of the day. Appointments let you reserve a morning or afternoon slot, the queue lets you take a number from home, and released documents are held at the counter until you claim them.',
        ],
        [
            'q' => 'Who can see my personal details?',
            'a' => 'Residents see only their own records. Barangay staff and the Punong Barangay see what they need to process your request — and the public JSON endpoints never expose e-mail addresses or other private fields.',
        ],
    ];
@endphp

{{-- ============================= PAGE HEAD ============================= --}}
<section class="hero">
    <div class="hero-inner">
        <span class="eyebrow">Services in detail</span>
        <h1>Everything the barangay does, <span class="gradient-text">explained module by module.</span></h1>
        <p class="lead">
            Two connected sides of the same system: a resident portal for everyday transactions and an
            officials console that keeps every request moving. Pick a module below to jump straight in.
        </p>

        <div class="cta">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                Create an account <x-icon name="chevron" size="16" />
            </a>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-lg">Back to home</a>
        </div>
    </div>
</section>

{{-- ============================= MODULES ============================= --}}
<section class="section">
    <div class="section-inner">
        <div class="section-head">
            <span class="eyebrow">The eight modules</span>
            <h2>What you can do — and who does what.</h2>
            <p>Each module works for residents on one side and barangay officials on the other.</p>
        </div>

        <div class="grid grid-2">
            @foreach ($features as $feature)
                <article class="feature-card">
                    <span class="ficon"><x-icon name="{{ $feature['icon'] }}" size="20" /></span>

                    <h3>{!! $feature['title'] !!}</h3>
                    <p style="margin-bottom:.85rem">{!! $feature['text'] !!}</p>

                    <div class="who-row">
                        @foreach ($feature['who'] as $audience)
                            <span class="badge {{ $audience === 'For officials' ? 'badge-violet' : 'badge-cyan' }}">
                                {{ $audience }}
                            </span>
                        @endforeach
                    </div>

                    <ul class="feat-list">
                        @foreach ($feature['bullets'] as $bullet)
                            <li><x-icon name="check" size="14" /> <span>{!! $bullet !!}</span></li>
                        @endforeach
                    </ul>

                    <a href="{{ $feature['route'] }}" class="btn btn-ghost btn-sm">
                        {!! $feature['action'] !!} <x-icon name="chevron" size="14" />
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================= COMPARISON ============================= --}}
<section class="section" style="padding-top:0">
    <div class="section-inner">
        <div class="section-head">
            <span class="eyebrow">Two sides, one record</span>
            <h2>Resident portal vs. officials console.</h2>
            <p>Residents start the work; officials finish it. Nothing is re-typed into a separate system.</p>
        </div>

        <div class="table-wrap card">
            <table class="table compare">
                <thead>
                    <tr>
                        <th>Capability</th>
                        <th class="center">Resident portal</th>
                        <th class="center">Officials console</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compare as $row)
                        <tr>
                            <td class="strong">{!! $row[0] !!}</td>
                            <td class="center">
                                @if ($row[1] === 'yes')
                                    <span class="badge badge-green badge-plain yes">Yes</span>
                                @elseif ($row[1] === 'no')
                                    <span class="badge badge-neutral badge-plain no">—</span>
                                @else
                                    <span class="badge badge-blue badge-plain">{{ $row[1] }}</span>
                                @endif
                            </td>
                            <td class="center">
                                @if ($row[2] === 'yes')
                                    <span class="badge badge-green badge-plain yes">Yes</span>
                                @elseif ($row[2] === 'no')
                                    <span class="badge badge-neutral badge-plain no">—</span>
                                @else
                                    <span class="badge badge-blue badge-plain">{{ $row[2] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row gap-2 mt-3">
            <a href="{{ route('resident.dashboard') }}" class="btn btn-primary">
                <x-icon name="login" size="16" /> Resident portal
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost">
                <x-icon name="shield" size="16" /> Officials console
            </a>
            <span class="small dim">Both use the same sign-in — the account role decides where you land.</span>
        </div>
    </div>
</section>

{{-- ============================= FAQ ============================= --}}
<section class="section" style="padding-top:0">
    <div class="section-inner">
        <div class="section-head">
            <span class="eyebrow">Frequently asked</span>
            <h2>Answers before you ask.</h2>
        </div>

        <div class="faq">
            @foreach ($faq as $item)
                <details>
                    <summary>
                        <span>{{ $item['q'] }}</span>
                        <x-icon name="chevron-down" size="16" />
                    </summary>
                    <p class="answer">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>

        <div class="card pad mt-4">
            <div class="row between">
                <div>
                    <h3 style="margin-bottom:.3rem">Still unsure where to start?</h3>
                    <p class="small muted" style="margin:0">
                        Create an account and the dashboard will guide you to the right module — or visit the
                        barangay hall during office hours and staff will file it for you.
                    </p>
                </div>
                <div class="row">
                    <a href="{{ route('register') }}" class="btn btn-primary">Create an account</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
