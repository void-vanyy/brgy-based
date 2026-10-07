@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Barangay settings — '.config('app.name'))
@section('topbar-title', 'Barangay settings')

@section('content')
    <div class="page-head">
        <div>
            <span class="eyebrow">Configuration</span>
            <h1>Barangay settings</h1>
            <p class="sub">Identity details shown across the portal, public site and printed documents.</p>
        </div>
        <div class="row">
            <a href="{{ route('home') }}" class="btn btn-ghost" target="_blank" rel="noopener">
                <x-icon name="eye" size="15" /> View public site
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        <div class="grid grid-23">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title"><x-icon name="sliders" /> Identity &amp; contact</h2>
                </div>
                <div class="card-body">
                    @foreach ($fields as $key => $meta)
                        <div class="field">
                            <label class="label" for="{{ $key }}">
                                {{ $meta['label'] }}@if ($key === 'barangay_name') <span class="req">*</span>@endif
                            </label>

                            @if ($key === 'barangay_address' || $key === 'barangay_motto')
                                <textarea id="{{ $key }}" name="{{ $key }}" class="textarea" rows="2"
                                    maxlength="220" placeholder="{{ $meta['placeholder'] }}">{{ old($key, $settings[$key] ?? '') }}</textarea>
                            @else
                                <input id="{{ $key }}" type="text" name="{{ $key }}" class="input" maxlength="220"
                                    placeholder="{{ $meta['placeholder'] }}"
                                    value="{{ old($key, $settings[$key] ?? '') }}">
                            @endif

                            <div class="help">{{ $meta['hint'] }}</div>
                            @error($key)<div class="error">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>

                <div class="card-foot row between">
                    <span class="tiny dim">Saved values are cached per request and used by both portals.</span>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" size="15" /> Save settings
                    </button>
                </div>
            </section>

            <div class="stack">
                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="eye" /> Live preview</h2>
                        <span class="badge badge-green">Used site-wide</span>
                    </div>
                    <div class="card-body">
                        <div class="row gap-2 items-start">
                            <span class="brand-mark" style="width:52px;height:52px;font-size:1.1rem">
                                {{ strtoupper(substr(preg_split('/\s+/', trim($settings['barangay_name'] ?? 'Barangay'))[0] ?? 'B', 0, 2)) }}
                            </span>
                            <div class="grow" style="min-width:0">
                                <div class="bold" style="font-size:1.05rem">{{ $settings['barangay_name'] ?? 'Barangay Sigla' }}</div>
                                <div class="small muted">{{ $settings['barangay_address'] ?? 'Barangay Hall address' }}</div>
                                <div class="tiny dim mt-1">{{ $settings['barangay_contact'] ?? 'Hotline / e-mail' }}</div>
                            </div>
                        </div>

                        <hr class="divider">

                        <div class="detail-list">
                            <div class="d"><dt>Punong Barangay</dt><dd>{{ $settings['barangay_capain'] ?? '—' }}</dd></div>
                            <div class="d"><dt>Motto</dt><dd style="text-align:right">{{ $settings['barangay_motto'] ?? '—' }}</dd></div>
                            <div class="d"><dt>Contact</dt><dd>{{ $settings['barangay_contact'] ?? '—' }}</dd></div>
                        </div>

                        <div class="alert alert-info mt-2 mb-0">
                            <x-icon name="info" size="17" />
                            <div class="small mb-0">
                                The Punong Barangay caption appears beneath the seal on certificates
                                and in the footer of printed documents.
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <div class="card-head">
                        <h2 class="card-title"><x-icon name="database" /> Stored keys</h2>
                    </div>
                    <div class="card-body">
                        <div class="detail-list">
                            @foreach ($fields as $key => $meta)
                                <div class="d">
                                    <dt class="mono">{{ $key }}</dt>
                                    <dd>
                                        @if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '')
                                            <span class="badge badge-green">set</span>
                                        @else
                                            <span class="badge badge-cyan">empty</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection
