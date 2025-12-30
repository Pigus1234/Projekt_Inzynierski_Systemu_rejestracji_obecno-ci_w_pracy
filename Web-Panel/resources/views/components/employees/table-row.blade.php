@props(['employee'])

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
            @can('employees.manage.update')
                <a href="{{ route('employees.edit', $employee) }}">
                    <x-ui.button class="px-3 py-2">Edytuj</x-ui.button>
                </a>
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
        </x-ui.action-group>
    </x-ui.table.data-cell>
</tr>
