{{--
    The "At the window" queue rows - contents of #activeList only.
    QueueController renders this after an ajax action so a ticket just called
    appears here without reloading the board.
--}}
                    @forelse ($active as $ticket)
                        <div class="queue-row" id="st-{{ $ticket->id }}">
                            <span class="qn">{{ $ticket->ticket_no }}</span>
                            <span class="grow">
                                {{ $ticket->name_on_ticket }}
                                <span class="tiny dim"> · {{ $ticket->serviceName() }}{{ $ticket->window ? ' · '.$ticket->window : '' }}</span>
                            </span>
                            <span class="badge {{ $badge[$ticket->status] ?? 'badge-amber' }}">{{ $ticket->statusLabel() }}</span>

                            <span class="row shrink-0" style="gap:.35rem">
                                @foreach ($actions[$ticket->status] ?? [] as $action)
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
                            <div class="ico"><x-icon name="ticket" size="22" /></div>
                            <h3>Window is free</h3>
                            <p class="mb-0">Called and in-progress tickets show up here.</p>
                        </div>
                    @endforelse
                    <p id="activeEmpty" class="small dim mb-0" hidden>
                        No one is at the window right now.
                    </p>
