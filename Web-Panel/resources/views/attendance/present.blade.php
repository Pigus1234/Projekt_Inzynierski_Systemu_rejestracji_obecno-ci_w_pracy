@extends('layouts.app')

@section('pageTitle', 'Obecni na terenie')
@section('pageSubtitle', 'Lista pracowników, których ostatnie zdarzenie to wejście.')

@section('content')
    <x-ui.card>
        <div class="text-sm text-slate-500">
            Łącznie: {{ $presentEmployees->count() }}
        </div>
    </x-ui.card>

    <x-ui.card class="mt-4">
        <x-ui.table>
            <x-slot:header>
                <tr class="text-left">
                    <x-ui.table.header-cell>Pracownik</x-ui.table.header-cell>
                    <x-ui.table.header-cell>Dział</x-ui.table.header-cell>
                    <x-ui.table.header-cell>RFID UID</x-ui.table.header-cell>
                    <x-ui.table.header-cell>Wejście</x-ui.table.header-cell>
                </tr>
            </x-slot:header>

            @forelse($presentEmployees as $employee)
                <tr>
                    <x-ui.table.data-cell>
                        <div class="text-sm font-semibold text-slate-900">{{ $employee->full_name }}</div>
                    </x-ui.table.data-cell>

                    <x-ui.table.data-cell>
                        <div class="text-sm text-slate-700">{{ $employee->department }}</div>
                    </x-ui.table.data-cell>

                    <x-ui.table.data-cell>
                        <div class="text-sm font-mono text-slate-700">{{ $employee->rfid_uid ?? '-' }}</div>
                    </x-ui.table.data-cell>

                    <x-ui.table.data-cell>
                        <div class="text-sm text-slate-700">
                            {{ $employee->last_entry_at ? \Illuminate\Support\Carbon::parse($employee->last_entry_at)->format('Y-m-d H:i:s') : '-' }}
                        </div>
                    </x-ui.table.data-cell>
                </tr>
            @empty
                <tr>
                    <x-ui.table.data-cell colspan="4">
                        <div class="text-sm text-slate-600">Brak osób obecnych.</div>
                    </x-ui.table.data-cell>
                </tr>
            @endforelse
        </x-ui.table>
    </x-ui.card>
@endsection
