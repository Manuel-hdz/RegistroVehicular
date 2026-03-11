@props([
    'type' => null,
    'size' => 26,
    'class' => '',
])

@php
    $resolvedType = $type ?: 'auto';
    $iconSize = (int) $size;
    $iconFile = match ($resolvedType) {
        'auto' => 'car-03-stroke-rounded.svg',
        'pickup' => 'pickup-01-stroke-rounded.svg',
        'furgoneta' => 'van-stroke-rounded.svg',
        'camion' => 'truck-stroke-rounded.svg',
        'transporte_personal' => 'bus-03-stroke-rounded.svg',
        'remolcable' => 'caravan-stroke-rounded.svg',
        'equipo_pesado' => 'tractor-stroke-rounded.svg',
        'trompo' => 'tanker-truck-stroke-rounded.svg',
        default => 'car-03-stroke-rounded.svg',
    };
    $iconUrl = asset('vendor/hugeicons/icons/' . $iconFile);
@endphp

<span class="{{ $class }}" style="display:inline-flex; align-items:center; justify-content:center; line-height:1;">
    <span
        aria-hidden="true"
        style="
            display:block;
            width: {{ $iconSize }}px;
            height: {{ $iconSize }}px;
            background-color: currentColor;
            -webkit-mask: url('{{ $iconUrl }}') center / contain no-repeat;
            mask: url('{{ $iconUrl }}') center / contain no-repeat;
        "
    ></span>
</span>
