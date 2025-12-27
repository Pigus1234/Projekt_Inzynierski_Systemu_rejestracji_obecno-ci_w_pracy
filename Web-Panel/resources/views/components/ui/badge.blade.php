@props([
    'variant' => 'danger',
])

@php
    $variantClasses = match ($variant) {
        default => 'border border-brandRed/25 bg-brandRed/5 text-brandRed',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.$variantClasses]) }}>
    {{ $slot }}
</span>
