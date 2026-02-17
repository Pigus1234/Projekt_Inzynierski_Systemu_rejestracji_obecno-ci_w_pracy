@props([
    'employee',
    'mode' => 'active',
])

<tr>
    <x-ui.table.data-cell>
        <div class="text-sm font-semibold text-slate-900">{{ $employee->full_name }}</div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell>
        <div class="text-sm text-slate-700">{{ $employee->department ?: '—' }}</div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell align="right">
        <x-ui.action-group>
            @if($mode === 'archived')
                @can('employees.manage.restore')
                    <form method="POST" action="{{ route('employees.restore', $employee->id) }}">
                        @csrf

                        <x-ui.button type="submit" class="px-3 py-2">
                            Przywróć
                        </x-ui.button>
                    </form>
                @else
                    <div class="text-sm font-semibold text-slate-400">Brak dostępu</div>
                @endcan
            @else
                @can('employees.manage.update')
                    <x-ui.button :href="route('employees.edit', $employee)" class="px-3 py-2">
                        Edytuj
                    </x-ui.button>
                @endcan

                @can('employees.manage.archive')
                    <form method="POST" action="{{ route('employees.archive', $employee) }}">
                        @csrf
                        @method('DELETE')

                        <x-ui.button type="submit" variant="danger" class="px-3 py-2">
                            Archiwizuj
                        </x-ui.button>
                    </form>
                @endcan
            @endif
        </x-ui.action-group>
    </x-ui.table.data-cell>
</tr>
