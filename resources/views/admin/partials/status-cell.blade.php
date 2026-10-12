{{-- The status cell for an admin record.

     Deliberately shared: the initial page load renders this, and so does the
     JSON response that rewrites the cell in place after an ajax action. One
     template means the two can never drift apart. --}}
<span class="badge {{ $badge }}">{{ $label }}</span>
@if (!empty($remark))
    <div class="tiny dim">{{ \Illuminate\Support\Str::limit($remark, 45) }}</div>
@endif
