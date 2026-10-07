@php
    $portal = 'admin';
@endphp
@extends('layouts.portal')

@section('title', 'Residents & Staff — '.config('app.name'))
@section('topbar-title', 'Residents & Staff')

@section('content')
    @php
        $roleBadge = ['admin' => 'badge-violet', 'staff' => 'badge-blue', 'resident' => 'badge-neutral'];
        $roleLabels = \App\Http\Controllers\Admin\UserController::ROLES;
        $statusBadge = ['active' => 'badge-green', 'suspended' => 'badge-rose'];
        $statusLabels = \App\Http\Controllers\Admin\UserController::STATUSES;
        $me = auth()->id();
        $currentRole = request('role');
        $currentStatus = request('status');
        $currentPage = $users->currentPage();
        $lastPage = $users->lastPage();
        $windowStart = max(1, $currentPage - 2);
        $windowEnd = min($lastPage, $currentPage + 2);
    @endphp

    <div class="page-head">
        <div>
            <span class="eyebrow">Records</span>
            <h1>Residents &amp; staff</h1>
            <p class="sub">Accounts in the barangay roster with their caseload at a glance.</p>
        </div>
        <div class="row">
            <span class="badge badge-rose">{{ $counts['suspended'] }} suspended</span>
        </div>
    </div>

    {{-- ============ tabs ============ --}}
    <div class="tabs">
        <a href="{{ route('admin.users.index') }}" class="tab {{ ! $currentRole && ! $currentStatus ? 'active' : '' }}">
            Everyone <span class="count">{{ $counts['all'] }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'resident']) }}" class="tab {{ $currentRole === 'resident' ? 'active' : '' }}">
            Residents <span class="count">{{ $counts['resident'] }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'staff']) }}" class="tab {{ $currentRole === 'staff' ? 'active' : '' }}">
            Staff <span class="count">{{ $counts['staff'] }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="tab {{ $currentRole === 'admin' ? 'active' : '' }}">
            Admins <span class="count">{{ $counts['admin'] }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="tab {{ $currentStatus === 'suspended' ? 'active' : '' }}">
            Suspended <span class="count">{{ $counts['suspended'] }}</span>
        </a>
    </div>

    {{-- ============ filters ============ --}}
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="toolbar">
            <div class="search-box grow">
                <input type="search" name="q" class="input" placeholder="Search name, e-mail or purok…"
                    value="{{ old('q', request('q')) }}">
            </div>

            <select name="role" class="select" style="width:auto; min-width:140px">
                <option value="">All roles</option>
                @foreach ($roleLabels as $key => $label)
                    <option value="{{ $key }}" @selected($currentRole === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="status" class="select" style="width:auto; min-width:140px">
                <option value="">All statuses</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected($currentStatus === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary"><x-icon name="search" size="15" /> Apply</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Reset</a>
            <span class="small dim grow right">{{ $users->total() }} account(s)</span>
        </div>
    </form>

    {{-- ============ roster ============ --}}
    <section class="card">
        @if ($users->isEmpty())
            <div class="empty">
                <div class="ico"><x-icon name="users" size="22" /></div>
                <h3>No accounts found</h3>
                <p>Nobody matches this filter. New registrations appear here automatically.</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Clear filters</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Caseload</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <span class="user-pill">
                                        <span class="avatar {{ $user->role === 'admin' ? 'violet' : ($user->role === 'staff' ? 'green' : 'neutral') }}">
                                            {{ $user->initials }}
                                        </span>
                                        <span>
                                            <span class="nm">
                                                {{ $user->name }}
                                                @if ($user->id === $me)
                                                    <span class="badge badge-cyan">You</span>
                                                @endif
                                            </span>
                                            <span class="rl">{{ $user->email }}</span>
                                        </span>
                                    </span>
                                    @if ($user->purok)
                                        <div class="tiny dim" style="padding-left:.55rem">Purok: {{ $user->purok }}</div>
                                    @endif
                                </td>
                                <td><span class="badge {{ $roleBadge[$user->role] ?? 'badge-neutral' }}">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span></td>
                                <td><span class="badge {{ $statusBadge[$user->status] ?? 'badge-neutral' }}">{{ $statusLabels[$user->status] ?? ucfirst($user->status) }}</span></td>
                                <td class="tiny dim nowrap">{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="nowrap">
                                    <div class="row" style="gap:.4rem">
                                        <span class="badge badge-rose" title="Complaints filed">{{ $user->complaints_count }} reports</span>
                                        <span class="badge badge-violet" title="Document requests">{{ $user->requests_count }} docs</span>
                                        <span class="badge badge-blue" title="Appointments">{{ $user->appointments_count }} appts</span>
                                    </div>
                                </td>
                                <td class="right nowrap">
                                    <div class="row" style="justify-content:flex-end; gap:.4rem">
                                        @if ($user->id === $me)
                                            <span class="tiny dim">Your account</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-info"
                                                data-modal-open="manageUser"
                                                data-url="{{ route('admin.users.update', $user) }}"
                                                data-name="{{ $user->name }}"
                                                data-role="{{ $user->role }}"
                                                data-status="{{ $user->status }}">
                                                <x-icon name="sliders" size="14" /> Manage
                                            </button>

                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                data-confirm="Delete {{ $user->name }}? Their complaints and requests will also be removed.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Delete account">
                                                    <x-icon name="trash" size="14" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="card-foot">
                    <nav class="pagination mt-0">
                        @if ($users->onFirstPage())
                            <span class="dim">&lsaquo;</span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @if ($windowStart > 1)
                            <a href="{{ $users->url(1) }}">1</a>
                            @if ($windowStart > 2)<span class="dim">…</span>@endif
                        @endif

                        @for ($i = $windowStart; $i <= $windowEnd; $i++)
                            @if ($i === $currentPage)
                                <span class="active">{{ $i }}</span>
                            @else
                                <a href="{{ $users->url($i) }}">{{ $i }}</a>
                            @endif
                        @endfor

                        @if ($windowEnd < $lastPage)
                            @if ($windowEnd < $lastPage - 1)<span class="dim">…</span>@endif
                            <a href="{{ $users->url($lastPage) }}">{{ $lastPage }}</a>
                        @endif

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}">&rsaquo;</a>
                        @else
                            <span class="dim">&rsaquo;</span>
                        @endif
                    </nav>
                </div>
            @endif
        @endif
    </section>

    {{-- ============ manage modal ============ --}}
    <div class="modal" id="manageUser">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-card">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Account</div>
                    <h3 id="userTitle" class="mb-0">Manage account</h3>
                </div>
                <button type="button" class="modal-x" data-modal-close>&times;</button>
            </div>

            <form method="POST" id="userForm" action="{{ route('admin.users.index') }}">
                @csrf
                @method('PATCH')

                <div class="modal-body stack">
                    <div class="alert alert-warning">
                        <x-icon name="shield" size="17" />
                        <div class="small mb-0">
                            Role decides what this account can access. Suspending signs the user out
                            and blocks the next login.
                        </div>
                    </div>

                    <div class="field">
                        <label class="label" for="userRole">Role <span class="req">*</span></label>
                        <select id="userRole" name="role" class="select">
                            @foreach ($roleLabels as $key => $label)
                                <option value="{{ $key }}" @selected(old('role') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="help">Administrators manage everything; staff handle day-to-day transactions.</div>
                        @error('role')<div class="error">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="userStatus">Status <span class="req">*</span></label>
                        <select id="userStatus" name="status" class="select">
                            @foreach ($statusLabels as $key => $label)
                                <option value="{{ $key }}" @selected(old('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" class="btn btn-ghost" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check" size="15" /> Save account
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('userForm');
            if (!form) return;

            document.querySelectorAll('[data-modal-open="manageUser"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    form.action = btn.getAttribute('data-url') || form.action;

                    document.getElementById('userTitle').textContent = btn.getAttribute('data-name') || 'Manage account';

                    var role = btn.getAttribute('data-role');
                    var status = btn.getAttribute('data-status');

                    if (role) document.getElementById('userRole').value = role;
                    if (status) document.getElementById('userStatus').value = status;
                });
            });
        });
    </script>
@endpush
