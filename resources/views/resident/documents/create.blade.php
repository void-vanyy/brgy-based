@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Request a Document — '.config('app.name'))
@section('topbar-title', 'Request a Document')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Records desk</span>
            <h1>Request a document</h1>
            <p class="sub">Pick what you need, tell us what it is for, and we will prepare it for pickup.</p>
        </div>
        <div class="row">
            <a href="{{ route('resident.documents.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to requests
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        <form class="card" method="POST" action="{{ route('resident.documents.store') }}">
            @csrf

            <div class="card-head">
                <h2 class="card-title"><x-icon name="file" /> Request details</h2>
                <span class="badge badge-cyan">Step 1 of 1</span>
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="field span-2">
                        <label class="label" for="doc_type">Document type <span class="req">*</span></label>
                        <select class="select" id="doc_type" name="doc_type">
                            <option value="">Choose a document…</option>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('doc_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('doc_type')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label class="label" for="purpose">Purpose <span class="req">*</span></label>
                        <input class="input" id="purpose" name="purpose" maxlength="190"
                               value="{{ old('purpose') }}" placeholder="e.g. Employment requirement, school enrolment, SSS pension…">
                        <div class="help">The records desk prints this on the document.</div>
                        @error('purpose')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="copies">Number of copies <span class="req">*</span></label>
                        <select class="select" id="copies" name="copies">
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" @selected((string) old('copies', '1') === (string) $i)>{{ $i }} cop{{ $i === 1 ? 'y' : 'ies' }}</option>
                            @endfor
                        </select>
                        @error('copies')
                            <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label">Estimated fee</label>
                        <div class="input mono" id="feePreview" aria-live="polite">₱30.00</div>
                        <div class="help">Paid at the barangay hall upon claiming.</div>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <x-icon name="info" size="16" />
                    <div>
                        <strong>Bring one valid ID.</strong>
                        <p class="small mb-0">Requests are usually released within 1–2 working days. You will see a DOC reference number right after submitting.</p>
                    </div>
                </div>
            </div>

            <div class="card-foot">
                <div class="row between">
                    <span class="tiny dim">Processing happens at the barangay records desk.</span>
                    <div class="row">
                        <a href="{{ route('resident.documents.index') }}" class="btn btn-ghost">Cancel</a>
                        <button class="btn btn-primary" type="submit">
                            <x-icon name="send" /> Submit request
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="database" /> Fee schedule</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Barangay Clearance</dt><dd class="mono">₱50.00</dd></div>
                        <div class="d"><dt>Business Permit Endorsement</dt><dd class="mono">₱50.00</dd></div>
                        <div class="d"><dt>All other certificates</dt><dd class="mono">₱30.00</dd></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clock" /> Processing flow</h2>
                </div>
                <div class="card-body">
                    <div class="stack-sm">
                        <div class="queue-row"><span class="qn">1</span><span class="grow">Request received — status <span class="badge badge-cyan">Pending</span></span></div>
                        <div class="queue-row"><span class="qn">2</span><span class="grow">Barangay secretary reviews your record</span></div>
                        <div class="queue-row"><span class="qn">3</span><span class="grow">Approved and signed by the Punong Barangay</span></div>
                        <div class="queue-row"><span class="qn">4</span><span class="grow">Claim at the records desk with your DOC number</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('doc_type');
        var preview = document.getElementById('feePreview');
        if (!select || !preview) return;

        var fee50 = @json(['barangay_clearance', 'business_permit_endorsement']);

        var refresh = function () {
            preview.textContent = '₱' + (fee50.indexOf(select.value) !== -1 ? '50.00' : '30.00');
        };

        select.addEventListener('change', refresh);
        refresh();
    });
</script>
@endpush
