@props([
    'align' => 'left',
])

@php
    $alignClass = $align === 'right' ? 'text-right' : 'text-left';
    $classes = "px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 {$alignClass}";
@endphp

<th {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</th>
