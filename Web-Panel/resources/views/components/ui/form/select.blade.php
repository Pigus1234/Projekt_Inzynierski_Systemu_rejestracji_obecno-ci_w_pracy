@props([
    'name',
    'label',
    'items' => [],
    'valueProperty' => 'id',
    'labelProperty' => 'name',
    'selectedValue' => null,
    'required' => false,
    'placeholder' => null,
])

@php
    $currentValue = old($name, $selectedValue);

    $selectClasses = 'mt-2 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brandBlue focus:ring-2 focus:ring-brandBlue/20';
@endphp

<div {{ $attributes }}>
    <label class="block text-sm font-medium text-slate-700" for="{{ $name }}">{{ $label }}</label>

    <select id="{{ $name }}" name="{{ $name }}" class="{{ $selectClasses }}" @if($required) required @endif>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($items as $item)
            @php
                $value = is_array($item) ? ($item[$valueProperty] ?? null) : ($item->{$valueProperty} ?? null);
                $text = is_array($item) ? ($item[$labelProperty] ?? null) : ($item->{$labelProperty} ?? null);
            @endphp

            <option value="{{ $value }}" @selected((string) $currentValue === (string) $value)>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="mt-2 text-sm text-brandRed">{{ $message }}</div>
    @enderror
</div>
