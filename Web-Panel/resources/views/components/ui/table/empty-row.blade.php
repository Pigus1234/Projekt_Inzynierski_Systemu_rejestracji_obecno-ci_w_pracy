@props([
    'colspan' => 1,
    'message',
])

<tr>
    <x-ui.table.data-cell :colspan="$colspan">
        <div class="py-6 text-sm text-slate-500">{{ $message }}</div>
    </x-ui.table.data-cell>
</tr>
