@props([
    'name',
    'label',
    'checked' => false,
])

@php
    $isChecked = old($name) !== null ? true : (bool) $checked;
@endphp

<label class="flex items-center gap-3 text-sm text-slate-700">
    <input
        type="checkbox"
        name="{{ $name }}"
        class="h-4 w-4 rounded border-slate-300 text-brandBlue focus:ring-brandBlue/30"
        @checked($isChecked)
    >
    {{ $label }}
</label>
