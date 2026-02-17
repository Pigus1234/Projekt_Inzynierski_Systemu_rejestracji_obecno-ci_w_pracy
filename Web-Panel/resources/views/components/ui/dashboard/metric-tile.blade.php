@props([
    'label',
    'value' => null,
    'fallback' => '—',
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white px-4 py-3']) }}>
    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
        {{ $label }}
    </div>

    <div class="mt-2 text-3xl font-semibold text-brandBlueDark">
        {{ is_int($value) ? $value : $fallback }}
    </div>
</div>
