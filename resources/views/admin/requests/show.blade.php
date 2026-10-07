@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', $docRequest->reference_no.' — '.config('app.name'))
@section('topbar-title', 'Document request')

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
        $keys = array_keys($statuses);
        $stepIndex = max(0, array_search($docRequest->status, $keys, true));
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Case {{ $docRequest->reference_no }}</span>
            <h1>{{ $docRequest->typeName() }}</h1>
            <p class="sub">
                Filed {{ $docRequest->created_at->format('M j, Y g:i A') }}
                by {{ $docRequest->resident?->name ?? 'a resident' }}
                @if ($docRequest->released_at) · released {{ $docRequest->released_at->format('M j, Y') }} @endif
            </p>
        </div>
        <div class="row">
            <span class="badge {{ $statusBadge[$docRequest->status] ?? 'badge-neutral' }}">{{ $docRequest->statusLabel() }}</span>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="15" /> Back to queue
            </a>
        </div>
    </div>

    <div class="grid grid-23">
        {{-- ==================== main ==================== --}}
        <div class="stack">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="file" /> Request details</h2>
                    @if ($docRequest->remarks)
                        <span class="badge badge-violet">Remarks on file</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d">
                            <dt>Reference</dt>
                            <dd class="row" style="gap:.45rem">
                                <span class="kbd mono">{{ $docRequest->reference_no }}</span>
                                <button type="button" class="btn btn-sm btn-ghost" data-copy="{{ $docRequest->reference_no }}">Copy</button>
                            </dd>
                        </div>
                        <div class="d"><dt>Document type</dt><dd>{{ $docRequest->typeName() }}</dd></div>
                        <div class="d"><dt>Purpose</dt><dd style="text-align:right">{{ $docRequest->purpose }}</dd></div>
                        <div class="d"><dt>Copies</dt><dd>{{ $docRequest->copies }}</dd></div>
                        <div class="d"><dt>Processing fee</dt><dd>₱ {{ number_format((float) $docRequest->fee, 2) }}</dd></div>
                        <div class="d"><dt>Filed</dt><dd>{{ $docRequest->created_at->format('M j, Y g:i A') }}</dd></div>
                        <div class="d"><dt>Released</dt><dd>{{ $docRequest->released_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="d"><dt>Last processed by</dt><dd>{{ $docRequest->processor?->name ?? 'Not yet processed' }}</dd></div>
                    </div>

                    @if ($docRequest->remarks)
                        <div class="alert alert-info mt-2 mb-0">
                            <x-icon name="info" size="17" />
                            <div><strong>Remarks</strong><p style="white-space:pre-wrap">{{ $docRequest->remarks }}</p></div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="sliders" /> Update the request</h2>
                    <span class="tiny dim">Stamped to your name on save</span>
                </div>

                <form method="POST" action="{{ route('admin.requests.update', $docRequest) }}">
                    @csrf
                    @method('PATCH')

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label class="label" for="status">Status <span class="req">*</span></label>
                                <select id="status" name="status" class="select">
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}" @selected(old('status', $docRequest->status) === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="help">Setting <strong>Released</strong> stamps the release date.</div>
                                @error('status')<div class="error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field">
                                <label class="label" for="copies_hint">Quick guide</label>
                                <div class="help" style="margin-top:0">
                                    under_review → approved → ready_for_release → released.
                                    Rejected requests return to the resident with your remarks.
                                </div>
                            </div>

                            <div class="field span-2">
                                <label class="label" for="remarks">Remarks</label>
                                <textarea id="remarks" name="remarks" class="textarea" rows="3"
                                    placeholder="Requirements still missing, claim instructions, reason for rejection…">{{ old('remarks', $docRequest->remarks) }}</textarea>
                                @error('remarks')<div class="error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-foot row between">
                        <span class="tiny dim">Residents see these remarks on their request tracker.</span>
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Save status
                        </button>
                    </div>
                </form>
            </section>
        </div>

        {{-- ==================== sidebar ==================== --}}
        <div class="stack">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="user" /> Requester</h2>
                </div>
                <div class="card-body">
                    <div class="row gap-2 items-center mb-2">
                        <span class="avatar lg violet">{{ $docRequest->resident?->initials ?? '—' }}</span>
                        <div class="grow" style="min-width:0">
                            <div class="bold">{{ $docRequest->resident?->name ?? 'Deleted account' }}</div>
                            <div class="tiny dim">{{ $docRequest->resident?->email ?? 'No e-mail on file' }}</div>
                        </div>
                    </div>

                    <div class="detail-list">
                        <div class="d"><dt>Mobile</dt><dd>{{ $docRequest->resident?->phone ?: '—' }}</dd></div>
                        <div class="d"><dt>Purok</dt><dd>{{ $docRequest->resident?->purok ?: '—' }}</dd></div>
                        <div class="d"><dt>Address</dt><dd>{{ $docRequest->resident?->address ?: '—' }}</dd></div>
                    </div>

                    @if ($docRequest->resident)
                        <a href="{{ route('admin.users.index', ['q' => $docRequest->resident->name]) }}"
                            class="btn btn-ghost btn-block btn-sm mt-2">
                            <x-icon name="users" size="15" /> Open resident record
                        </a>
                    @endif
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="clock" /> Processing trail</h2>
                </div>
                <div class="card-body">
                    <div class="row between">
                        <span class="tiny dim">Progress</span>
                        <span class="badge {{ $statusBadge[$docRequest->status] ?? 'badge-neutral' }}">{{ $docRequest->statusLabel() }}</span>
                    </div>

                    <div class="steps mt-1">
                        @foreach ($keys as $index)
                            <span class="st {{ $docRequest->status === 'rejected' && $index === $stepIndex ? 'rejected' : ($index <= $stepIndex ? 'on' : '') }}"></span>
                        @endforeach
                    </div>

                    <div class="row between mt-1 tiny dim">
                        <span>Filed</span>
                        <span>{{ $docRequest->released_at?->format('M j') ?? 'Release' }}</span>
                    </div>

                    <hr class="divider">

                    <div class="stack-sm">
                        <div class="row between small">
                            <span class="muted">Requested on</span>
                            <span>{{ $docRequest->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="row between small">
                            <span class="muted">Days in queue</span>
                            <span>{{ (int) $docRequest->created_at->diffInDays(now()) }} day(s)</span>
                        </div>
                        <div class="row between small">
                            <span class="muted">Fee collected</span>
                            <span>₱ {{ number_format((float) $docRequest->fee, 2) }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
