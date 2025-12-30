@props(['employee'])

@php
    $canRestore = auth()->user()?->can('employees.manage.restore') ?? false;
@endphp

<tr>
    <x-ui.table.data-cell>
        <div class="text-sm font-semibold text-slate-900">{{ $employee->full_name }}</div>
        <div class="text-sm text-slate-500">{{ $employee->rfid_uid }}</div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell>
        <div class="text-sm text-slate-700">{{ $employee->department ?: '—' }}</div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell align="right">
        <x-ui.action-group>
            @if($canRestore)
                <form method="POST" action="{{ route('employees.restore', $employee->id) }}">
                    @csrf

                    <x-ui.button type="submit" class="px-3 py-2">
                        Przywróć
                    </x-ui.button>
                </form>
            @else
                <div class="text-sm font-semibold text-slate-400">Brak dostępu</div>
            @endif
        </x-ui.action-group>
    </x-ui.table.data-cell>
</tr>
