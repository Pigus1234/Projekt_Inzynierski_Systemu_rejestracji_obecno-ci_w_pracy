@props([
    'description',
    'actionUrl' => null,
    'actionLabel' => null,
    'actionVariant' => 'primary',
    'actionButtonClass' => null,
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-between gap-3']) }}>
    <div class="text-sm text-slate-500">
        {{ $description }}
    </div>

    @isset($actions)
        <div class="flex items-center justify-end gap-2">
            {{ $actions }}
        </div>
    @elseif(filled($actionUrl) && filled($actionLabel))
        <x-ui.button
            :href="$actionUrl"
            :variant="$actionVariant"
            @class([$actionButtonClass])
        >
            {{ $actionLabel }}
        </x-ui.button>
    @endif
</div>
