@php
    $portal = 'resident';
@endphp
@extends('layouts.portal')

@section('title', 'Freedom Wall — '.config('app.name'))
@section('topbar-title', 'Freedom Wall')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Community space</span>
            <h1>Freedom Wall</h1>
            <p class="sub">A public board where residents speak freely — every post is published anonymously.</p>
        </div>
        <div class="row">
            <span class="badge badge-violet"><x-icon name="lock" size="13" /> Anonymous by default</span>
        </div>
    </div>

    <div class="grid grid-23">
        {{-- ============ feed ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="send" /> Share something</h2>
                    <span class="tiny dim">{{ auth()->user()->initials }} &middot; posting as anonymous</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('resident.freedom-wall.store') }}">
                        @csrf

                        <div class="field">
                            <label class="label" for="message">Your message <span class="req">*</span></label>
                            <textarea class="textarea" id="message" name="message" maxlength="1000"
                                      placeholder="Got an idea for the barangay? A shout-out for a volunteer? A concern you want heard?">{{ old('message') }}</textarea>
                            <div class="help">3 to 1,000 characters. Your name is never shown on the wall.</div>
                            @error('message')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="topic">Topic <span class="dim">(optional)</span></label>
                            <input class="input" id="topic" name="topic" maxlength="60"
                                   value="{{ old('topic') }}" placeholder="e.g. Road safety, Youth program, Budget">
                            @error('topic')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row between">
                            <span class="pin-note"><x-icon name="shield" size="13" /> Post as anonymous resident</span>
                            <button class="btn btn-primary" type="submit">
                                <x-icon name="send" /> Post to wall
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($posts->isEmpty())
                <div class="card">
                    <div class="empty">
                        <div class="ico"><x-icon name="chat" size="26" /></div>
                        <h3>The wall is quiet</h3>
                        <p>Be the first resident to start a conversation for the community.</p>
                    </div>
                </div>
            @else
                <div class="stack">
                    @foreach ($posts as $post)
                        <article class="wall-card">
                            <div class="meta">
                                <span class="avatar sm violet"><x-icon name="user" size="14" /></span>
                                <strong class="small">{{ $post->displayAuthor() }}</strong>
                                <span>&middot;</span>
                                <span>{{ $post->created_at->diffForHumans() }}</span>
                                @if ($post->topic)
                                    <span class="chip">{{ $post->topic }}</span>
                                @endif
                                @if ($post->user_id === auth()->id())
                                    <span class="badge badge-cyan">Yours</span>
                                @endif
                            </div>

                            <div class="msg">{{ $post->message }}</div>

                            <div class="foot">
                                <form method="POST" action="{{ route('resident.freedom-wall.react', $post) }}">
                                    @csrf
                                    <button class="react" type="submit">
                                        <x-icon name="heart" size="14" /> {{ $post->reactions }}
                                    </button>
                                </form>
                                <span class="tiny dim">reaction{{ $post->reactions === 1 ? '' : 's' }}</span>

                                <span class="grow"></span>

                                @if ($post->user_id === auth()->id())
                                    <form method="POST" action="{{ route('resident.freedom-wall.destroy', $post) }}"
                                          data-confirm="Delete your post? This cannot be undone.">
                                        @csrf
                                        <button class="react" type="submit">
                                            <x-icon name="trash" size="14" /> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($posts->hasPages())
                @php($pageUrl = fn (int $p) => url()->current().'?'.http_build_query(array_merge(request()->query(), ['page' => $p])))
                @php($window = range(max(1, $posts->currentPage() - 2), max(1, min($posts->lastPage(), $posts->currentPage() + 2))))
                <nav class="pagination" aria-label="Pagination">
                    @if ($posts->onFirstPage())
                        <span>&laquo;</span>
                    @else
                        <a href="{{ $posts->previousPageUrl() }}">&laquo;</a>
                    @endif

                    @foreach ($window as $p)
                        @if ($p === $posts->currentPage())
                            <span class="active">{{ $p }}</span>
                        @else
                            <a href="{{ $pageUrl($p) }}">{{ $p }}</a>
                        @endif
                    @endforeach

                    @if ($posts->hasMorePages())
                        <a href="{{ $posts->nextPageUrl() }}">&raquo;</a>
                    @else
                        <span>&raquo;</span>
                    @endif
                </nav>
            @endif
        </div>

        {{-- ============ sidebar ============ --}}
        <div class="stack">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="shield" /> House rules</h2>
                </div>
                <div class="card-body">
                    <div class="stack-sm">
                        <div class="row items-start">
                            <span class="badge badge-cyan">01</span>
                            <span class="small muted grow">Stay constructive — critique ideas and services, not people.</span>
                        </div>
                        <div class="row items-start">
                            <span class="badge badge-cyan">02</span>
                            <span class="small muted grow">No threats, hate speech or private information about others.</span>
                        </div>
                        <div class="row items-start">
                            <span class="badge badge-cyan">03</span>
                            <span class="small muted grow">Anonymous does not mean unaccountable — officials may take down violations.</span>
                        </div>
                        <div class="row items-start">
                            <span class="badge badge-cyan">04</span>
                            <span class="small muted grow">For urgent concerns use the complaint form or call the barangay hotline.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="layers" /> Start with a topic</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach (['Road safety', 'Garbage collection', 'Youth & SK', 'Health programs', 'Livelihood', 'Barangay budget'] as $suggestion)
                            <button class="chip" type="button" data-topic="{{ $suggestion }}">{{ $suggestion }}</button>
                        @endforeach
                    </div>
                    <p class="help mb-0">Pick a topic to tag your post before publishing.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="info" /> How the wall works</h2>
                </div>
                <div class="card-body">
                    <div class="detail-list">
                        <div class="d"><dt>Identity</dt><dd>Hidden from everyone</dd></div>
                        <div class="d"><dt>Reactions</dt><dd>Open to all residents</dd></div>
                        <div class="d"><dt>Deletion</dt><dd>Author only</dd></div>
                        <div class="d"><dt>Moderation</dt><dd>Barangay officials</dd></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var topic = document.getElementById('topic');
        if (!topic) return;

        document.querySelectorAll('[data-topic]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                topic.value = chip.getAttribute('data-topic');
                topic.focus();
                window.toast && window.toast('Topic set: ' + chip.getAttribute('data-topic'), 'info');
            });
        });
    });
</script>
@endpush
