@props([
    'align' => 'left',
])

@php
    $alignClass = $align === 'right' ? 'text-right' : 'text-left';
    $classes = "px-4 py-3 {$alignClass}";
@endphp

<td {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</td>
