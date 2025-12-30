@props([
    'href',
    'activePattern' => null,
])

@php
    $isActive = $activePattern ? request()->routeIs($activePattern) : false;

    $activeClasses = 'rounded-xl border border-brandBlue/20 bg-brandBlue/5 px-4 py-2 text-sm font-semibold text-brandBlueDark hover:bg-brandBlue/10';
    $inactiveClasses = 'rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50';
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->class([$isActive ? $activeClasses : $inactiveClasses]) }}
>
    {{ $slot }}
</a>
