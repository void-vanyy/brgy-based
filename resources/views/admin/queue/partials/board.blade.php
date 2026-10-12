{{--
    The Now-serving summary board - contents of #queueBoard only.
    QueueController renders this after an ajax action so the counts move
    with the tickets without reloading the page.
--}}
                <div class="board">
                    <div class="now-label">Now serving</div>
                    <div class="now-num" id="boardNow">{{ $nowServing?->ticket_no ?? '—' }}</div>
                    {{-- See admin/dashboard: a served ticket must never sit
                         above "No ticket called yet". --}}
                    <div class="now-win" id="boardWin">
                        @if (! $nowServing)
                            No ticket called yet
                        @elseif ($nowServing->window)
                            {{ $nowServing->window }} &middot; {{ $nowServing->serviceName() }}
                        @else
                            {{ $nowServing->serviceName() }} &middot; window not assigned
                        @endif
                    </div>
                    <div class="split">
                        <div><span>Waiting</span><b id="boardWaiting">{{ $waiting->count() }}</b></div>
                        <div><span>At window</span><b id="boardActive">{{ $active->count() }}</b></div>
                        <div><span>Served</span><b id="boardServed">{{ $doneCount }}</b></div>
                        <div><span>Skipped</span><b id="boardSkipped">{{ $skippedCount }}</b></div>
                    </div>
                </div>
