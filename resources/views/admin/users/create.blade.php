@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'New barangay account — '.config('app.name'))
@section('topbar-title', 'New barangay account')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Records</span>
            <h1>New barangay account</h1>
            <p class="sub">Open a console sign-in for an officer of one barangay.</p>
        </div>
        <div class="row">
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">
                <x-icon name="chevron" size="14" /> Back to accounts
            </a>
        </div>
    </div>

    {{-- A plain post, not data-ajax: this form has five fields and the server's
         own wording for each one is worth showing next to the field it belongs
         to, which a toast cannot do. Without JavaScript it still works. --}}
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <div class="grid grid-23">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="users" /> Account details</h2>
                </div>

                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label class="label" for="name">Full name <span class="req">*</span></label>
                            <input id="name" type="text" name="name" class="input" maxlength="120"
                                placeholder="e.g. Juan Dela Cruz" value="{{ old('name') }}" required>
                            @error('name')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="email">E-mail address <span class="req">*</span></label>
                            <input id="email" type="email" name="email" class="input" maxlength="190"
                                placeholder="e.g. captain@tagapo.barangay.gov" value="{{ old('email') }}" required>
                            <div class="help">This is what the officer types to sign in.</div>
                            @error('email')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="barangay_id">Barangay <span class="req">*</span></label>
                            <select id="barangay_id" name="barangay_id" class="select" required>
                                <option value="">Choose a barangay…</option>
                                @foreach ($barangays as $barangay)
                                    <option value="{{ $barangay->id }}"
                                        @selected((int) old('barangay_id', $defaultBarangayId ?? 0) === $barangay->id)>
                                        {{ $barangay->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="help">
                                The console opens filtered to this barangay. It is their home base,
                                not a ceiling — they can widen it to all 18 from the header filter.
                            </div>
                            @error('barangay_id')<div class="error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field span-2">
                            <label class="label" for="account-password">Password <span class="req">*</span></label>
                            <div class="pw-wrap">
                                <input class="input" type="password" id="account-password" name="password"
                                    placeholder="At least 8 characters"
                                    minlength="8" maxlength="64"
                                    autocomplete="new-password" required
                                    aria-describedby="pw-meter-hint pw-meter-rules">
                                @include('partials.password-toggle', ['for' => 'account-password', 'what' => 'password'])
                            </div>

                            {{-- live strength meter - rules mirror App\Support\PasswordPolicy --}}
                            @include('partials.password-strength', [
                                'pwId'      => 'account-password',
                                'confirmId' => 'account-password-confirmation',
                                'matchId'   => 'pw-match',
                                'meterId'   => 'pw-meter',
                                'email'     => null,
                                'optional'  => false,
                            ])

                            @foreach ($errors->get('password') as $message)
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @endforeach
                        </div>

                        <div class="field span-2">
                            <label class="label" for="account-password-confirmation">Confirm password <span class="req">*</span></label>
                            <div class="pw-wrap">
                                <input class="input" type="password" id="account-password-confirmation" name="password_confirmation"
                                    placeholder="Repeat the password"
                                    minlength="8" maxlength="64"
                                    autocomplete="new-password" required
                                    aria-describedby="pw-match">
                                @include('partials.password-toggle', ['for' => 'account-password-confirmation', 'what' => 'password confirmation'])
                            </div>
                            <div class="pw-match" id="pw-match" role="status" hidden></div>
                            @error('password_confirmation')
                                <div class="error"><x-icon name="alert" size="13" /> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">The account can sign in the moment it is created.</span>
                    <div class="row">
                        <button type="submit" class="btn btn-primary">
                            <x-icon name="check" size="15" /> Create account
                        </button>
                    </div>
                </div>
            </section>

            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="shield" /> What this account is</h2>
                    </div>
                    <div class="card-body stack">
                        <div class="alert alert-warning">
                            <x-icon name="info" size="17" />
                            <div class="small mb-0">
                                An officer account for a single barangay. It opens the same City
                                Administrator console every officer uses, starting on this barangay.
                            </div>
                        </div>

                        <p class="small dim mb-0">
                            Hand the officer the e-mail address and the password you set here —
                            nothing is sent to them automatically.
                        </p>

                        <hr class="divider">

                        <p class="small dim mb-0">
                            Accounts are edited from the roster: <strong>Manage</strong> changes the
                            barangay, the interface or the status, and the trash icon removes one.
                            Residents do not get an account this way — they register themselves.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection
