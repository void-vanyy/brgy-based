{{--
    The progress tracker card body on a complaint.

    Self-contained apart from $complaint and $events: ComplaintController
    renders this exact partial after an ajax save to rewrite the card in
    place, so the initial page load and the in-place update cannot drift.

    $events comes from App\Support\ComplaintTracker::events().
--}}
<div class="card-head">
    <h2 class="card-title"><x-icon name="clock" /> Progress tracker</h2>
    <span class="badge badge-neutral">{{ $events->count() }} entr{{ $events->count() === 1 ? 'y' : 'ies' }}</span>
</div>

<div class="card-body">
    <div class="timeline">
        @foreach ($events as $event)
            <div class="timeline-item {{ $loop->last
                ? ($complaint->status === 'closed' ? 'reject' : ($complaint->status === 'resolved' ? 'done' : 'active'))
                : 'done' }}">
                <div class="timeline-head">
                    <span class="timeline-title">{{ $event['title'] }}</span>
                    <span class="badge {{ \App\Http\Controllers\Admin\ComplaintController::STATUS_BADGES[$event['status']] ?? 'badge-neutral' }}">{{ $event['label'] }}</span>
                    <span class="timeline-time">{{ $event['at']->format('M j, g:i A') }}</span>
                </div>
                @if ($event['note'])
                    <div class="timeline-note">{{ $event['note'] }}</div>
                @endif
                <div class="tiny dim mt-1">Logged by {{ $event['author'] }}</div>
            </div>
        @endforeach
    </div>

    <hr class="divider">

    <h3 class="small bold">Add a note without changing the status</h3>
    <form method="POST" action="{{ route('admin.complaints.note', $complaint) }}" class="mt-2" data-ajax>
        @csrf

        <div class="field">
            <label class="label" for="note_title">Note title <span class="dim">(optional)</span></label>
            <input id="note_title" type="text" name="title" class="input" maxlength="140"
                placeholder="e.g. Called the complainant" value="{{ old('title') }}">
            @error('title')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="field">
            <label class="label" for="note_body">Details <span class="req">*</span></label>
            <textarea id="note_body" name="note" class="textarea" rows="3"
                placeholder="What happened, who was contacted, what comes next">{{ old('note') }}</textarea>
            @error('note')<div class="error">{{ $message }}</div>@enderror
        </div>

        <div class="row">
            <button type="submit" class="btn btn-accent">
                <x-icon name="send" size="15" /> Add to tracker
            </button>
            <span class="tiny dim">Notes keep the resident informed but leave the status untouched.</span>
        </div>
    </form>
</div>
