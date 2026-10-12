{{--
    The top five waiting tickets - contents of #nextList only.
    QueueController renders this after an ajax action so the peek at the
    line agrees with the Waiting list without a reload.
--}}
                    @forelse ($nextInLine as $ticket)
                        <div class="queue-row" id="next-{{ $ticket->id }}">
                            <span class="qn">{{ $ticket->ticket_no }}</span>
                            <span class="grow">
                                {{ $ticket->name_on_ticket }}
                                <span class="tiny dim"> · {{ $ticket->serviceName() }}</span>
                            </span>
                            <span class="tiny dim nowrap">~{{ $ticket->waitMinutes() }} min</span>
                        </div>
                    @empty
                        <div class="empty" style="padding:1.4rem .6rem">
                            <div class="ico"><x-icon name="check-circle" size="22" /></div>
                            <h3>Line is clear</h3>
                            <p class="mb-0">Issue a walk-in ticket below or wait for residents to file online.</p>
                        </div>
                    @endforelse
                    <p id="nextEmpty" class="small dim mb-0" hidden>Line is clear — nobody is queued.</p>
