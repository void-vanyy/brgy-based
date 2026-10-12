{{--
    The "Processing trail" card on a document request.

    Self-contained apart from $docRequest: DocumentRequestController renders
    this exact partial after an ajax status save to rewrite the card in
    place, so both renders cannot drift.
--}}
<div class="card-head">
    <h2 class="card-title"><x-icon name="clock" /> Processing trail</h2>
</div>
<div class="card-body">
    <div class="row between">
        <span class="tiny dim">Progress</span>
        {{-- Rewritten in place after an ajax save; see DocumentRequestController. --}}
        <span data-status-cell>
            @include('admin.partials.status-cell', [
                'badge' => \App\Models\DocumentRequest::STATUS_BADGES[$docRequest->status] ?? 'badge-neutral',
                'label' => $docRequest->statusLabel(),
                'remark' => null,
            ])
        </span>
    </div>

    @php
        $trailKeys = array_keys(\App\Models\DocumentRequest::STATUSES);
        $trailIndex = max(0, array_search($docRequest->status, $trailKeys, true));
    @endphp

    {{-- Position, not the status key: array_keys() yields 'pending', 'under_review'
         etc, and PHP 8 compares a non-numeric string against a number as text,
         so '$key <= $stepIndex' is always false and no step ever lit up. --}}
    <div class="steps mt-1">
        @foreach ($trailKeys as $i => $trailStatus)
            <span class="st {{ $docRequest->status === 'rejected' && $i === $trailIndex ? 'rejected' : ($i <= $trailIndex ? 'on' : '') }}"></span>
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
            <span>&#8369; {{ number_format((float) $docRequest->fee, 2) }}</span>
        </div>
    </div>
</div>
