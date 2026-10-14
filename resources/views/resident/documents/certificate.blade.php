@php
    /*
     * The paper itself. Everything printed here comes from either the request
     * (resident, type, purpose, fee) or the city-office branding on the
     * settings screen, which already describes itself as the text "printed on
     * certificates" - this is where that promise is finally kept.
     */
    $settings = $settings ?? [];
    $resident = $document->resident;
    $barangay = $resident->barangay;

    $cityName = $settings['barangay_name'] ?? config('app.name');
    $officeAddress = $settings['barangay_address'] ?? '';
    $officeContact = $settings['barangay_contact'] ?? '';
    $signatory = $settings['barangay_capain'] ?? '';

    $barangayName = $barangay?->name ?? 'Barangay';

    // The setting is stored as "Santa Rosa City", so the letterhead line drops
    // the trailing word rather than reading "City of Santa Rosa City".
    $cityLine = 'City of '.preg_replace('/\s+city$/i', '', $cityName);

    // The roster stores purok both as "3" and as "Purok 3".
    $purok = $resident->purok
        ? 'Purok '.preg_replace('/^purok\s+/i', '', $resident->purok)
        : null;

    // "8 Ilang-Ilang Street, Zone 1, Purok 3, Barangay Kanluran, Santa Rosa City, Laguna"
    $residence = collect([
        $resident->address,
        $purok,
        'Barangay '.$barangayName,
        $cityName,
        'Laguna',
    ])->filter()->implode(', ');

    $ageLine = $resident->birth_date
        ? $resident->birth_date->age.' years old'
        : 'of legal age';

    // Date the paper carries: the release stamp when there is one, otherwise
    // the last time the record moved.
    $issued = $document->released_at ?? $document->updated_at;

    $backUrl = $portal === 'admin'
        ? route('admin.requests.show', $document)
        : route('resident.documents.show', $document);

    // "13th day of October 2026" - the phrasing used on issued documents here.
    $day = (int) $issued->format('j');
    $suffix = match (true) {
        $day % 10 === 1 && $day !== 11 => 'st',
        $day % 10 === 2 && $day !== 12 => 'nd',
        $day % 10 === 3 && $day !== 13 => 'rd',
        default => 'th',
    };
@endphp

@extends('layouts.portal')

@section('title', $document->reference_no.' — Certificate — '.config('app.name'))
@section('topbar-title', 'Certificate')

@section('content')
    <div class="page-head print-hide">
        <div>
            <span class="eyebrow">Certificate</span>
            <h1>{{ $document->typeName() }}</h1>
            <p class="sub">
                This is the document the reference number stands for. Print it, or choose
                <em>Save as PDF</em> in the print dialog.
            </p>
        </div>
        <div class="row">
            <button class="btn btn-primary" type="button" onclick="window.print()">
                <x-icon name="printer" size="16" /> Print / Save as PDF
            </button>
            <a href="{{ $backUrl }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to request
            </a>
        </div>
    </div>

    <article class="cert">
        <header class="cert-head">
            <svg class="cert-seal" viewBox="0 0 200 200" role="img"
                 aria-label="Official seal of Barangay {{ $barangayName }}">
                <defs>
                    {{-- Two arcs on the same radius; text on the lower one is
                         laid out left-to-right so it stays upright. --}}
                    <path id="sealTop" d="M 26,100 A 74,74 0 0 1 174,100" fill="none" />
                    <path id="sealBottom" d="M 26,100 A 74,74 0 0 0 174,100" fill="none" />
                </defs>

                <circle cx="100" cy="100" r="94" fill="none" stroke="currentColor" stroke-width="2.4" />
                <circle cx="100" cy="100" r="87" fill="none" stroke="currentColor" stroke-width="0.9" />

                <text class="seal-arc"><textPath href="#sealTop" startOffset="50%" text-anchor="middle">BARANGAY {{ strtoupper($barangayName) }}</textPath></text>
                <text class="seal-arc"><textPath href="#sealBottom" startOffset="50%" text-anchor="middle">SANTA ROSA CITY &#183; LAGUNA</textPath></text>

                {{-- Separators between the two arcs. --}}
                <circle cx="26" cy="100" r="3.4" fill="currentColor" />
                <circle cx="174" cy="100" r="3.4" fill="currentColor" />

                <circle cx="100" cy="100" r="48" fill="none" stroke="currentColor" stroke-width="1.1" />
                <polygon fill="currentColor" points="100,76 105.64,92.23 122.83,92.58 109.13,102.97 114.11,119.42 100,109.6 85.89,119.42 90.87,102.97 77.17,92.58 94.36,92.23" />

                @if ($barangay?->psgc_code)
                    <text class="seal-code" x="100" y="137" text-anchor="middle">PSGC {{ $barangay->psgc_code }}</text>
                @endif
            </svg>

            <div class="cert-letterhead">
                <span class="cl">Republic of the Philippines</span>
                <span class="cl">Province of Laguna</span>
                <span class="cl cl-city">{{ $cityLine }}</span>
                <span class="cl cl-brgy">Barangay {{ $barangayName }}</span>
                <span class="cl cl-office">Office of the Punong Barangay</span>
            </div>
        </header>

        <div class="cert-rule" aria-hidden="true"></div>

        <h2 class="cert-title">To Whom It May Concern:</h2>

        <div class="cert-body">
            <p>
                This is to certify that <strong>{{ strtoupper($resident->name) }}</strong>,
                {{ $ageLine }}, {{ $residence }}, is known to this Office to be a person of
                good standing in this community.
            </p>

            @include('resident.documents.certificate-body', [
                'document' => $document,
                'barangayName' => $barangayName,
                'cityName' => $cityName,
            ])

            <p>
                This certification is issued upon the request of the above-named person for
                <em>{{ $document->purpose }}</em>, and is valid only for the purpose stated above.
            </p>

            <p>
                <strong>IN WITNESS WHEREOF</strong>, I hereunto set my hand this
                {{ $day }}<sup>{{ $suffix }}</sup> day of {{ $issued->format('F') }}
                {{ $issued->year }} at Barangay {{ $barangayName }}, {{ $cityName }}, Laguna.
            </p>
        </div>

        <div class="cert-sign">
            <div class="cert-sign-line"></div>
            <span class="cert-sign-name">{{ $signatory ?: '________________________' }}</span>
            <span class="cert-sign-role">Punong Barangay</span>
        </div>

        <footer class="cert-foot">
            <div class="cert-foot-row">
                <span><span class="k">Reference</span> <span class="mono">{{ $document->reference_no }}</span></span>
                <span><span class="k">Document</span> {{ $document->typeName() }}</span>
                <span><span class="k">Copies</span> {{ $document->copies }}</span>
                <span><span class="k">Fee</span> &#8369;{{ number_format((float) $document->fee, 2) }}</span>
                <span><span class="k">Issued</span> {{ $issued->format('M d, Y') }}</span>
            </div>

            @if ($officeAddress || $officeContact)
                <div class="cert-foot-contact">
                    @if ($officeAddress)<span>{{ $officeAddress }}</span>@endif
                    @if ($officeContact)<span>{{ $officeContact }}</span>@endif
                </div>
            @endif

            <div class="cert-foot-verify">
                This document was issued electronically and carries reference number
                <span class="mono">{{ $document->reference_no }}</span>. Verify it with the
                barangay records desk.
            </div>
        </footer>
    </article>
@endsection
