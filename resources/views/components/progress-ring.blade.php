@props([
    'radius'   => 54,
    'stroke'   => 8,
    'progress' => 0,
    'label'    => '',
    'color'    => '#7C5CFF', // Default to nuance purple
])

@php
    $normalizedRadius = $radius - $stroke * 2;
    $circumference    = $normalizedRadius * 2 * M_PI;
    $strokeDashoffset = $circumference - ($progress / 100) * $circumference;
    $size             = $radius * 2;
@endphp

<div class="relative inline-flex items-center justify-center">
    <svg height="{{ $size }}" width="{{ $size }}" role="img" aria-label="{{ $label }}">
        <circle stroke="rgba(255,255,255,0.05)" fill="transparent"
            stroke-width="{{ $stroke }}" r="{{ $normalizedRadius }}"
            cx="{{ $radius }}" cy="{{ $radius }}" />
        <circle stroke="{{ $color }}" fill="transparent"
            stroke-width="{{ $stroke }}"
            stroke-dasharray="{{ $circumference }} {{ $circumference }}"
            style="stroke-dashoffset: {{ $strokeDashoffset }};
                   transform: rotate(-90deg); transform-origin: 50% 50%;
                   transition: stroke-dashoffset 0.6s ease;"
            r="{{ $normalizedRadius }}"
            cx="{{ $radius }}" cy="{{ $radius }}" />
    </svg>
    <span class="absolute text-lg font-bold text-white">
        {{ number_format($progress, 0) }}%
    </span>
</div>
