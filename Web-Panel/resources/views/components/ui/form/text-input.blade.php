@props([
    'name',
    'label',
    'type' => 'text',
    'autocomplete' => null,
    'required' => false,
    'value' => null,
])

@php
    $inputClasses = 'mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brandBlue focus:ring-2 focus:ring-brandBlue/20';
@endphp

<label class="block text-sm font-medium text-slate-700" for="{{ $name }}">
    {{ $label }}
</label>

<input
    id="{{ $name }}"
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ old($name, $value) }}"
    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
    @if($required) required @endif
    {{ $attributes->merge(['class' => $inputClasses]) }}
>

@error($name)
    <div class="mt-2 text-sm text-brandRed">{{ $message }}</div>
@enderror
