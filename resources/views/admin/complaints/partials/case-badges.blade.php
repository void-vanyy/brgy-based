{{--
    The case header badges (priority + status), rewritten in place after an
    ajax status save. Self-contained so ComplaintController can render it too.
--}}
<span class="badge {{ \App\Http\Controllers\Admin\ComplaintController::PRIORITY_BADGES[$complaint->priority] ?? 'badge-neutral' }}">
    {{ \App\Http\Controllers\Admin\ComplaintController::PRIORITIES[$complaint->priority] ?? $complaint->priority }} priority
</span>
<span class="badge {{ \App\Http\Controllers\Admin\ComplaintController::STATUS_BADGES[$complaint->status] ?? 'badge-neutral' }}">
    {{ \App\Http\Controllers\Admin\ComplaintController::STATUSES[$complaint->status] ?? $complaint->status }}
</span>
