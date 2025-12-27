@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $variantClasses = match ($variant) {
        'danger' => 'border border-brandRed/30 bg-brandRed/5 text-brandRed hover:bg-brandRed/10',
        default => 'bg-brandBlue text-white hover:bg-brandBlueDark',
    };

    $baseClasses = 'inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition';
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses.' '.$variantClasses]) }}>
    {{ $slot }}
</button>
