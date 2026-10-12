{{--
    The "Waiting" queue rows - contents of #waitingList only.
    QueueController renders this after an ajax action so a ticket that moved
    off the list disappears without reloading the board.
--}}
                    @forelse ($waiting as $ticket)
                        <div class="queue-row" id="wt-{{ $ticket->id }}">
                            <span class="qn">{{ $ticket->ticket_no }}</span>
                            <span class="grow">
                                {{ $ticket->name_on_ticket }}
                                <span class="tiny dim">
                                    · {{ $ticket->serviceName() }}
                                    · #{{ $ticket->positionInLine() }} in line
                                </span>
                            </span>
                            <span class="tiny dim nowrap">~{{ $ticket->waitMinutes() }} min</span>

                            <span class="row shrink-0" style="gap:.35rem">
                                @foreach ($actions['waiting'] as $action)
                                    <form method="POST" data-ajax action="{{ route('admin.queue.update', $ticket) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $action[1] }}">
                                        <button type="submit" class="btn btn-sm {{ $action[2] }}">{{ $action[0] }}</button>
                                    </form>
                                @endforeach
                            </span>
                        </div>
                    @empty
                        <div class="empty" style="padding:1.6rem .6rem">
                            <div class="ico"><x-icon name="users" size="22" /></div>
                            <h3>Nobody is waiting</h3>
                            <p class="mb-0">The queue is empty — call the next resident as they arrive.</p>
                        </div>
                    @endforelse
                    <p id="waitingEmpty" class="small dim mb-0" hidden>
                        Nobody is waiting — the line is clear.
                    </p>
