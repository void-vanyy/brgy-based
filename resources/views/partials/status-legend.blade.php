{{--
    Colour key for the badges used across the tracking lists.

    The status palette is genuinely consistent app-wide - cyan means "waiting on
    the barangay", amber "in progress", violet "paused", green "done", rose
    "stopped or urgent" - but nothing on screen ever says so, which is exactly
    what trips up a first-time visitor.

    Collapsed by default so it answers the newcomer without adding to the
    visual load of an already busy list.

    Optional vars on include:
        showPriority  - add the urgent/high/medium/low key (complaints pages)
--}}
@php($showPriority = $showPriority ?? false)

<details class="legend">
    <summary>
        <x-icon name="info" size="15" />
        What do the colours mean?
        <x-icon name="chevron-down" size="15" class="chev" />
    </summary>

    <div class="legend-body">
        <div class="legend-grid">
            <div class="legend-item">
                <span class="badge badge-cyan">Waiting</span>
                <div>
                    <b>Waiting on the barangay</b>
                    <span>Received or queued — it is logged, but work has not started.</span>
                </div>
            </div>

            <div class="legend-item">
                <span class="badge badge-amber">In progress</span>
                <div>
                    <b>Being worked on now</b>
                    <span>Under review, confirmed, or your turn at the window.</span>
                </div>
            </div>

            <div class="legend-item">
                <span class="badge badge-violet">On hold</span>
                <div>
                    <b>Paused or already claimed</b>
                    <span>Waiting on something else, or the item was collected.</span>
                </div>
            </div>

            <div class="legend-item">
                <span class="badge badge-green">Done</span>
                <div>
                    <b>Finished — no action needed</b>
                    <span>Approved, resolved or released. Just claim it.</span>
                </div>
            </div>

            <div class="legend-item">
                <span class="badge badge-rose">Stopped</span>
                <div>
                    <b>Ended, or needs you urgently</b>
                    <span>Rejected, cancelled, closed or skipped — also marks top priority.</span>
                </div>
            </div>

            <div class="legend-item">
                <span class="badge badge-blue">Label</span>
                <div>
                    <b>Category, not status</b>
                    <span>Blue is descriptive — a type or grouping, never a stage.</span>
                </div>
            </div>
        </div>

        @if ($showPriority)
            <hr class="divider" style="margin:.9rem 0 .8rem">
            <div class="legend-grid">
                <div class="legend-item">
                    <span class="badge badge-rose">Urgent</span>
                    <div><b>Immediate threat</b><span>To life, safety or property.</span></div>
                </div>
                <div class="legend-item">
                    <span class="badge badge-amber">High</span>
                    <div><b>Affecting households now</b><span>Needs attention this week.</span></div>
                </div>
                <div class="legend-item">
                    <span class="badge badge-blue">Medium</span>
                    <div><b>Recurring, can be scheduled</b><span>Handled in the normal run of work.</span></div>
                </div>
                <div class="legend-item">
                    <span class="badge badge-neutral">Low</span>
                    <div><b>Minor concern</b><span>Can wait for regular action.</span></div>
                </div>
            </div>
        @endif

        <p class="legend-note">
            Every badge also spells out its label in words, so colour is never the only clue.
        </p>
    </div>
</details>
