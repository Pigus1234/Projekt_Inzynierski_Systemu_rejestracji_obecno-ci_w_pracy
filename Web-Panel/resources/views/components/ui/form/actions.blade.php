@props([
    'cancelUrl',
    'cancelLabel' => 'Anuluj',
    'submitLabel' => 'Zapisz',
    'cancelVariant' => null,
])

<div class="flex items-center justify-end gap-3">
    @if($cancelVariant)
        <x-ui.button :href="$cancelUrl" :variant="$cancelVariant">
            {{ $cancelLabel }}
        </x-ui.button>
    @else
        <a href="{{ $cancelUrl }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
            {{ $cancelLabel }}
        </a>
    @endif

    <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
</div>
