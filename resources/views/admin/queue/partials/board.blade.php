{{--
    The Now-serving summary board - contents of #queueBoard only.
    QueueController renders this after an ajax action so the counts move
    with the tickets without reloading the page.
--}}
                <div class="board">
                    <div class="now-label">Now serving</div>
                    <div class="now-num" id="boardNow">{{ $nowServing?->ticket_no ?? '—' }}</div>
                    <div class="now-win" id="boardWin">{{ $nowServing?->window ?? 'No ticket called yet' }}</div>
                    <div class="split">
                        <div><span>Waiting</span><b id="boardWaiting">{{ $waiting->count() }}</b></div>
                        <div><span>At window</span><b id="boardActive">{{ $active->count() }}</b></div>
                        <div><span>Served</span><b id="boardServed">{{ $doneCount }}</b></div>
                        <div><span>Skipped</span><b id="boardSkipped">{{ $skippedCount }}</b></div>
                    </div>
                </div>
