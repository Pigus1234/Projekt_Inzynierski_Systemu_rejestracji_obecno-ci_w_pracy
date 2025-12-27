@props([
    'eyebrow' => null,
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl bg-white border border-slate-200 p-6']) }}>
    @if($eyebrow)
        <div class="text-xs uppercase tracking-wider text-slate-500">{{ $eyebrow }}</div>
    @endif

    @if($title)
        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $title }}</div>
    @endif

    <div @class([($eyebrow || $title) ? 'mt-5' : null])>
        {{ $slot }}
    </div>
</div>
