@props([
    'cancelUrl',
    'cancelLabel' => 'Anuluj',
    'submitLabel' => 'Zapisz',
])

<div class="flex items-center justify-end gap-3">
    <a href="{{ $cancelUrl }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
        {{ $cancelLabel }}
    </a>

    <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
</div>
