@props([
    'variant' => 'success',
])

@php
    $variantClasses = match ($variant) {
        'error' => 'border-brandRed/30 bg-brandRed/5 text-brandRed',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        default => 'border-slate-200 bg-white text-slate-700',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border px-4 py-3 text-sm {$variantClasses}"]) }}>
    {{ $slot }}
</div>
