@props(['lottery', 'result', 'size' => 'fs-1'])
@php
    $numbers = $result->numbers ?? [];
@endphp
@if ($lottery->has_series)
    <span class="font-monospace {{ $size }} fw-bold">{{ $numbers[0] ?? '----' }}</span>
    @if (! empty($numbers[1]))
        <span class="badge bg-warning text-dark ms-2 align-middle">Serie {{ $numbers[1] }}</span>
    @endif
@else
    <ul class="list-inline d-inline-flex flex-wrap gap-2 mb-0 align-middle">
        @foreach ($numbers as $numero)
            <li class="list-inline-item m-0">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white font-monospace fw-bold" style="width: 2.75rem; height: 2.75rem;">{{ $numero }}</span>
            </li>
        @endforeach
    </ul>
@endif
