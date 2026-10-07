@props(['name', 'class' => '', 'size' => '18'])

<svg class="ico {{ $class }}" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
     stroke-linejoin="round" aria-hidden="true">
    @foreach ($segments as $segment)
        @if (str_starts_with($segment, 'circle:'))
            @php($c = explode(',', substr($segment, 7)))
            <circle cx="{{ $c[0] }}" cy="{{ $c[1] }}" r="{{ $c[2] }}" />
        @elseif (str_starts_with($segment, 'ellipse:'))
            @php($c = explode(',', substr($segment, 8)))
            <ellipse cx="{{ $c[0] }}" cy="{{ $c[1] }}" rx="{{ $c[2] }}" ry="{{ $c[3] }}" />
        @elseif (str_starts_with($segment, 'rect:'))
            @php($c = explode(',', substr($segment, 5)))
            <rect x="{{ $c[0] }}" y="{{ $c[1] }}" width="{{ $c[2] }}" height="{{ $c[3] }}" rx="{{ $c[4] ?? 0 }}" />
        @else
            <path d="{{ $segment }}" />
        @endif
    @endforeach
</svg>
