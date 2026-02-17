@extends('layouts.app')

@section('pageTitle', 'Pulpit')
@section('pageSubtitle', 'Panel po zalogowaniu. Tu będą kluczowe informacje systemowe.')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card eyebrow="Stan zakładu" title="Pracownicy na terenie">
            <div class="flex items-end gap-3">
                <div class="text-4xl font-semibold text-brandBlueDark">
                    {{ is_int($presentEmployeesCount) ? $presentEmployeesCount : '—' }}
                </div>
                <div class="pb-1 text-sm text-slate-500">w tej chwili</div>
            </div>

            <div class="mt-4 text-sm text-slate-500">
                {{ is_int($presentEmployeesCount) ? 'Wartość liczona na podstawie ostatniego zdarzenia wejścia/wyjścia.' : 'Dane pojawią się po podpięciu rejestracji RFID i zapisu zdarzeń.' }}
            </div>
        </x-ui.card>

        <x-ui.card eyebrow="Statystyki" title="Odbicia (ostatnie 24h)">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.dashboard.metric-tile
                    label="Wszystkie odbicia"
                    :value="$attendanceEventsCountLast24Hours"
                />

                <x-ui.dashboard.metric-tile
                    label="Nieznane karty"
                    :value="$unknownCardAttemptsCountLast24Hours"
                />
            </div>

            <div class="mt-4 text-sm text-slate-500">
                {{ is_int($attendanceEventsCountLast24Hours) ? 'Zakres: ostatnie 24 godziny.' : 'Dane pojawią się po podpięciu bazy danych i zdarzeń odbić.' }}
            </div>
        </x-ui.card>

        <x-ui.card eyebrow="Status systemu" title="Połączenia">
            <div class="space-y-3">
                <x-ui.dashboard.status-row label="Aplikacja" value="OK" state="ok" />
                <x-ui.dashboard.status-row label="Baza danych" :value="$databaseStatusValue" :state="$databaseStatusState" />
                <x-ui.dashboard.status-row label="RFID / Arduino" :value="$rfidStatusValue" :state="$rfidStatusState" />
            </div>
        </x-ui.card>
    </div>
@endsection
