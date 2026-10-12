{{--
    The "Request details" card on a document request.

    Self-contained apart from $docRequest: DocumentRequestController renders
    this exact partial after an ajax status save, because the released date,
    the processor stamp and the remarks block all move with the status.
--}}
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
        <div class="d"><dt>Copies</dt><dd>{{ $docRequest->copies }} — {{ $docRequest->feeBreakdown() }}</dd></div>
        <div class="d"><dt>Processing fee</dt><dd>&#8369; {{ number_format((float) $docRequest->fee, 2) }}</dd></div>
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
