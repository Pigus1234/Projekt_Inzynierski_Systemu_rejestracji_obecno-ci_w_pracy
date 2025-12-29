@props([
    'name',
    'title',
    'items' => [],
    'valueProperty' => 'id',
    'labelProperty' => 'label',
    'selectedValues' => [],
])

@php
    $currentValues = old($name, $selectedValues);

    if (! is_array($currentValues)) {
        $currentValues = [];
    }
@endphp

<div class="rounded-xl border border-slate-200 p-4">
    <div class="text-sm font-semibold text-slate-900">{{ $title }}</div>

    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
        @foreach($items as $item)
            @php
                $value = is_array($item) ? ($item[$valueProperty] ?? null) : ($item->{$valueProperty} ?? null);
                $text = is_array($item) ? ($item[$labelProperty] ?? null) : ($item->{$labelProperty} ?? null);
            @endphp

            <label class="flex items-start gap-3 text-sm text-slate-700">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $value }}"
                    class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brandBlue focus:ring-brandBlue/30"
                    @checked(in_array($value, $currentValues))
                >
                <span>{{ $text }}</span>
            </label>
        @endforeach
    </div>

    @error($name)
        <div class="mt-2 text-sm text-brandRed">{{ $message }}</div>
    @enderror
</div>
