{{--
    Show / hide toggle for a password input.

    The button is inert on its own. app.js finds every [data-pw-toggle] on
    boot and wires it to the input whose id it names, so no per-form script
    is needed and the toggle cannot drift out of sync with its input.

    Wrap the input so the button has a positioned parent, then render the
    toggle straight after it:

        <div class="pw-wrap">
            <input class="input" type="password" id="reg-password" ...>
            @include('partials.password-toggle', ['for' => 'reg-password'])
        </div>

    Security notes:
      * type="button" — the control can never submit the form.
      * app.js re-masks the input on submit, so the plaintext value is not
        left sitting in the DOM once the request has gone out.
      * autocomplete on the input is left exactly as the form needs it; the
        toggle only ever flips the input's type.
      * aria-pressed / aria-controls keep it usable with a screen reader.
--}}
@php
    $for = $for ?? null;
    $what = $what ?? 'password';
@endphp
@if ($for)
    <button type="button" class="pw-toggle" data-pw-toggle="{{ $for }}"
            data-what="{{ $what }}"
            aria-controls="{{ $for }}" aria-pressed="false"
            aria-label="Show {{ $what }}" title="Show {{ $what }}">
        <x-icon name="eye" size="16" class="pw-i-show" />
        <x-icon name="eye-off" size="16" class="pw-i-hide" />
    </button>
@endif
