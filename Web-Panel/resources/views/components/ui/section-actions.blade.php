@props([
    'description',
    'actionUrl',
    'actionLabel',
])

<div class="flex items-center justify-between gap-3">
    <div class="text-sm text-slate-500">
        {{ $description }}
    </div>

    <a href="{{ $actionUrl }}">
        <x-ui.button>{{ $actionLabel }}</x-ui.button>
    </a>
</div>
