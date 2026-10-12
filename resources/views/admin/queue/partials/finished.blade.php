{{--
    The "Finished today" card contents. QueueController renders this after an
    ajax action so a ticket just marked done shows up without a reload.
--}}
            <div class="card-head">
                <h2 class="card-title"><x-icon name="clipboard" /> Finished today</h2>
                <span class="badge badge-green">{{ $doneCount }} served</span>
            </div>

            @if ($finished->isEmpty())
                <div class="empty">
                    <div class="ico"><x-icon name="clipboard" size="22" /></div>
                    <h3>Nothing completed yet</h3>
                    <p class="mb-0">Tickets move here once they are marked done, skipped or no-show.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Resident</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Closed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($finished as $ticket)
                                <tr>
                                    <td class="mono nowrap">{{ $ticket->ticket_no }}</td>
                                    <td class="strong">{{ $ticket->name_on_ticket }}</td>
                                    <td class="tiny dim nowrap">{{ $ticket->serviceName() }}</td>
                                    <td><span class="badge {{ $badge[$ticket->status] ?? 'badge-neutral' }}">{{ $ticket->statusLabel() }}</span></td>
                                    <td class="tiny dim nowrap">
                                        {{ ($ticket->served_at ?? $ticket->updated_at)->format('g:i A') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
