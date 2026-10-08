{{--
    Password strength meter + live requirement checklist.

    The five rules below mirror App\Support\PasswordPolicy - the server is the
    authority, this only reflects it. If you change one, change the other.

    @include('partials.password-strength', [
        'pwId'      => 'reg-password',
        'confirmId' => 'reg-password-confirmation',
        'matchId'   => 'pw-match',
        'email'     => null,          // only needed when the form has no e-mail input
        'optional'  => false,         // true = leaving both fields blank is allowed
        'blankHint' => 'Start typing to see how strong it is',
    ])

    Pair it with a confirm field that carries class "js-pw-match" and id {{ $matchId }}.
--}}
@php
    $pwId = $pwId ?? 'password';
    $confirmId = $confirmId ?? 'password_confirmation';
    $matchId = $matchId ?? 'pw-match';
    $meterId = $meterId ?? 'pw-meter';
    $optional = $optional ?? false;
    $email = $email ?? null;
    $blankHint = $blankHint ?? 'Start typing to see how strong it is';
    $commonList = \App\Support\PasswordPolicy::COMMON;
@endphp

<div class="pw-meter lv0 js-pw-meter" id="{{ $meterId }}"
     data-pw="{{ $pwId }}"
     data-confirm="{{ $confirmId }}"
     data-match="{{ $matchId }}"
     data-email="{{ $email }}"
     data-optional="{{ $optional ? '1' : '0' }}"
     data-blank-hint="{{ $blankHint }}"
     data-common="{{ json_encode($commonList) }}">

    <div class="pw-track" id="{{ $meterId }}-track" role="meter" aria-label="Password strength"
         aria-valuemin="0" aria-valuemax="5" aria-valuenow="0" aria-valuetext="Empty">
        <span class="pw-seg"></span>
        <span class="pw-seg"></span>
        <span class="pw-seg"></span>
        <span class="pw-seg"></span>
        <span class="pw-seg"></span>
    </div>

    <div class="pw-meta">
        <span class="pw-label" id="{{ $meterId }}-label">Password strength</span>
        <span class="pw-hint" id="{{ $meterId }}-hint">{{ $blankHint }}</span>
    </div>

    <ul class="pw-rules" id="{{ $meterId }}-rules">
        <li data-rule="len"><span class="dot"></span>At least 8 characters</li>
        <li data-rule="case"><span class="dot"></span>Upper &amp; lower case letters</li>
        <li data-rule="num"><span class="dot"></span>At least one number</li>
        <li data-rule="sym"><span class="dot"></span>At least one symbol</li>
        <li data-rule="orig"><span class="dot"></span>Not common or predictable</li>
    </ul>
</div>
