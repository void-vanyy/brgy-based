{{--
    The "case facts" sidebar rows (reference, status, priority, handler,
    resolved) - the children only, not the wrapping list. The page wraps them
    in <div class="detail-list" id="caseFacts">, and ComplaintController
    rewrites that container's innerHTML after an ajax status save.

    Needs nothing beyond $complaint, so both callers render identically.
--}}
<div class="d">
    <dt>Reference</dt>
    <dd class="row" style="gap:.45rem">
        <span class="kbd mono">{{ $complaint->reference_no }}</span>
        <button type="button" class="btn btn-sm btn-ghost" data-copy="{{ $complaint->reference_no }}">Copy</button>
    </dd>
</div>
<div class="d"><dt>Status</dt><dd><span class="badge {{ \App\Http\Controllers\Admin\ComplaintController::STATUS_BADGES[$complaint->status] ?? 'badge-neutral' }}">{{ \App\Http\Controllers\Admin\ComplaintController::STATUSES[$complaint->status] ?? $complaint->status }}</span></dd></div>
<div class="d"><dt>Priority</dt><dd><span class="badge {{ \App\Http\Controllers\Admin\ComplaintController::PRIORITY_BADGES[$complaint->priority] ?? 'badge-neutral' }}">{{ \App\Http\Controllers\Admin\ComplaintController::PRIORITIES[$complaint->priority] ?? $complaint->priority }}</span></dd></div>
<div class="d">
    <dt>Handler</dt>
    <dd>{{ $complaint->assignee?->name ?? 'Unassigned' }}</dd>
</div>
<div class="d"><dt>Resolved</dt><dd>{{ $complaint->resolved_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
