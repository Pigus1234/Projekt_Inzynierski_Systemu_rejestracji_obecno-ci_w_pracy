@extends('layouts.app')

@section('pageTitle', 'Changelog odbić')
@section('pageSubtitle', 'Wejścia i wyjścia z możliwością filtrowania.')

@section('content')
    <x-ui.card>
        <div class="text-sm text-slate-500">
            Zdarzenia: {{ $attendanceEvents->total() }}
        </div>
    </x-ui.card>

    <x-ui.card class="mt-4">
        <form method="GET" action="{{ route('attendance.changelog') }}" class="grid grid-cols-1 gap-5 md:grid-cols-12 md:items-end">
            <div class="md:col-span-6">
                <x-ui.form.text-input
                    name="employee"
                    label="Pracownik"
                    :value="$filters['employee']"
                    placeholder="Np. Jan Kowalski"
                />
            </div>

            <div class="md:col-span-6">
                <x-ui.form.text-input
                    name="department"
                    label="Dział"
                    :value="$filters['department']"
                    placeholder="Np. Magazyn"
                />
            </div>

            <div class="md:col-span-4">
                <x-ui.form.attendance-event-type-select
                    name="eventType"
                    label="Typ"
                    :selectedValue="$filters['eventType']"
                />
            </div>

            <div class="md:col-span-4">
                <x-ui.form.text-input
                    name="dateFrom"
                    label="Od"
                    type="date"
                    :value="$filters['dateFrom']"
                />
            </div>

            <div class="md:col-span-4">
                <x-ui.form.text-input
                    name="dateTo"
                    label="Do"
                    type="date"
                    :value="$filters['dateTo']"
                />
            </div>

            <div class="md:col-span-12 flex justify-end gap-2">
                <x-ui.button type="submit">Filtruj</x-ui.button>
                <x-ui.button :href="route('attendance.changelog')" variant="secondary">Wyczyść</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="mt-4">
        <x-ui.table>
            <x-slot:header>
                <tr class="text-left">
                    <x-ui.table.header-cell>Data</x-ui.table.header-cell>
                    <x-ui.table.header-cell>Typ</x-ui.table.header-cell>
                    <x-ui.table.header-cell>Pracownik</x-ui.table.header-cell>
                    <x-ui.table.header-cell>Dział</x-ui.table.header-cell>
                </tr>
            </x-slot:header>

            @forelse($attendanceEvents as $attendanceEvent)
                <tr>
                    <x-ui.table.data-cell>
                        <div class="text-sm text-slate-700">
                            {{ $attendanceEvent->occurred_at?->format('Y-m-d H:i:s') ?? '-' }}
                        </div>
                    </x-ui.table.data-cell>

                    <x-ui.table.data-cell>
                        <div class="text-sm font-semibold text-slate-900">
                            {{ $attendanceEvent->event_type->label() }}
                        </div>
                    </x-ui.table.data-cell>

                    <x-ui.table.data-cell>
                        <div class="text-sm text-slate-900">{{ $attendanceEvent->employee?->full_name ?? '-' }}</div>
                    </x-ui.table.data-cell>

                    <x-ui.table.data-cell>
                        <div class="text-sm text-slate-700">{{ $attendanceEvent->employee?->department ?? '-' }}</div>
                    </x-ui.table.data-cell>
                </tr>
            @empty
                <tr>
                    <x-ui.table.data-cell colspan="4">
                        <div class="text-sm text-slate-600">Brak zdarzeń.</div>
                    </x-ui.table.data-cell>
                </tr>
            @endforelse
        </x-ui.table>

        <div class="mt-4">
            {{ $attendanceEvents->links() }}
        </div>
    </x-ui.card>
@endsection
