@props([
    'label',
    'value' => '—',
    'state' => 'neutral',
])

@php
    $valueClasses = match ($state) {
        'ok' => 'text-emerald-700',
        'error' => 'text-brandRed',
        default => 'text-slate-500',
    };
@endphp

<div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
    <div class="text-sm text-slate-700">{{ $label }}</div>
    <div class="text-sm font-semibold {{ $valueClasses }}">{{ $value }}</div>
</div>
