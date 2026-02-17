@props([
    'name',
    'label',
    'items' => [],
    'valueProperty' => null,
    'labelProperty' => null,
    'selectedValue' => null,
    'placeholder' => '—',
    'hint' => null,
    'colSpan' => 2,
    'required' => false,
])

@php
    $isRequired = in_array($required, [true, 1, '1', 'true', 'on', 'yes'], true);
    $fieldId = $attributes->get('id', $name);
    $selected = old($name, $selectedValue);
    $containerSpanClass = (int) $colSpan >= 2 ? 'md:col-span-2' : 'md:col-span-1';
@endphp

<div class="min-w-0 {{ $containerSpanClass }} flex flex-col gap-2 md:flex-row md:items-center md:gap-5">
    <label class="block text-sm font-medium text-slate-700 md:w-1/2" for="{{ $fieldId }}">
        {{ $label }}
        @if($isRequired)
            <span class="text-brandRed">*</span>
        @endif
    </label>

    <div class="min-w-0 w-full md:w-1/2 md:ml-auto">
        <select
            id="{{ $fieldId }}"
            name="{{ $name }}"
            {{ $attributes->except(['id', 'class'])->merge([
                'class' => 'mt-2 md:mt-0 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brandBlue focus:ring-2 focus:ring-brandBlue/20 ' . ($attributes->get('class') ?? ''),
            ]) }}
            @if($isRequired) required @endif
        >
            <option value="">{{ $placeholder }}</option>

            @foreach($items as $item)
                @php
                    $optionValue = $valueProperty ? data_get($item, $valueProperty) : $item;
                    $optionLabel = $labelProperty ? data_get($item, $labelProperty) : $item;
                @endphp

                <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>

        @if(!empty($hint))
            <div class="mt-2 text-sm text-amber-700">{{ $hint }}</div>
        @endif

        @error($name)
            <div class="mt-2 text-sm text-brandRed">{{ $message }}</div>
        @enderror
    </div>
</div>
