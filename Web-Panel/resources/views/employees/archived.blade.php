@extends('layouts.app')

@section('pageTitle', 'Archiwum pracowników')
@section('pageSubtitle', 'Przywracanie zarchiwizowanych pracowników.')

@section('content')
    <div class="w-full max-w-none space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Pracownicy" title="Archiwum">
            <x-ui.section-actions
                description="Pracownicy zarchiwizowani (soft delete)."
                :actionUrl="route('employees.index')"
                actionLabel="Wróć do listy"
                actionVariant="secondary"
                actionButtonClass="px-3 py-2"
            />

            <x-ui.table>
                <x-slot:header>
                    <tr class="text-left">
                        <x-ui.table.header-cell>Pracownik</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Dział</x-ui.table.header-cell>
                        <x-ui.table.header-cell align="right">Akcje</x-ui.table.header-cell>
                    </tr>
                </x-slot:header>

                @forelse($employees as $employee)
                    <x-employees.table-row :employee="$employee" mode="archived" />
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
