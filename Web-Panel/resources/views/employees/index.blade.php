@extends('layouts.app')

@section('pageTitle', 'Pracownicy')
@section('pageSubtitle', 'Zarządzanie pracownikami i archiwizacja.')

@section('content')
    <div class="w-full max-w-none  space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Pracownicy" title="Lista pracowników">
            <x-ui.section-actions description="Aktywni pracownicy w systemie.">
                <x-slot:actions>
                    <x-ui.button :href="route('employees.archived')" class="px-3 py-2">Archiwum</x-ui.button>
                    <x-ui.button :href="route('employees.create')">Dodaj pracownika</x-ui.button>
                </x-slot:actions>
            </x-ui.section-actions>

            <x-ui.table>
                <x-slot:header>
                    <tr class="text-left">
                        <x-ui.table.header-cell>Pracownik</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Dział</x-ui.table.header-cell>
                        <x-ui.table.header-cell align="right">Akcje</x-ui.table.header-cell>
                    </tr>
                </x-slot:header>

                @forelse($employees as $employee)
                    <x-employees.table-row :employee="$employee" />
                @empty
                    <x-ui.table.empty-row colspan="3" message="Archiwum jest puste." />
                @endforelse
            </x-ui.table>

            <div class="mt-5">
                {{ $employees->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
