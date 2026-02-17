@props([
    'type' => 'button',
    'variant' => 'primary',
    'href' => null,
])

@php
    $resolvedHref = $href ?? $attributes->get('href');
    $isLink = filled($resolvedHref);

    $variantClasses = match ($variant) {
        'secondary' => 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
        'danger' => 'border border-brandRed/30 bg-brandRed/5 text-brandRed hover:bg-brandRed/10',
        default => 'bg-brandBlue text-white hover:bg-brandBlueDark',
    };

    $baseClasses = 'inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition';

    $mergedAttributes = $attributes->merge(['class' => $baseClasses.' '.$variantClasses]);
@endphp

@if($isLink)
    <a href="{{ $resolvedHref }}" {{ $mergedAttributes->except(['type', 'href']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $mergedAttributes->except(['href']) }}>
        {{ $slot }}
    </button>
@endif
