@extends('layouts.app')

@section('pageTitle', 'Archiwum pracowników')
@section('pageSubtitle', 'Przywracanie zarchiwizowanych pracowników.')

@section('content')
    <div class="max-w-5xl space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Pracownicy" title="Archiwum">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm text-slate-500">
                    Pracownicy zarchiwizowani (soft delete).
                </div>

                <a href="{{ route('employees.index') }}">
                    <x-ui.button class="px-3 py-2">Wróć do listy</x-ui.button>
                </a>
            </div>

            <x-ui.table>
                <x-slot:header>
                    <tr class="text-left">
                        <x-ui.table.header-cell>Pracownik</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Dział</x-ui.table.header-cell>
                        <x-ui.table.header-cell align="right">Akcje</x-ui.table.header-cell>
                    </tr>
                </x-slot:header>

                @forelse($employees as $employee)
                    <x-employees.archived-table-row :employee="$employee" />
                @empty
                    <tr>
                        <x-ui.table.data-cell colspan="3">
                            <div class="py-6 text-sm text-slate-500">Archiwum jest puste.</div>
                        </x-ui.table.data-cell>
                    </tr>
                @endforelse
            </x-ui.table>

            <div class="mt-5">
                {{ $employees->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
