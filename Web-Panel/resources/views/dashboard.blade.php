@extends('layouts.app')

@section('pageTitle', 'Dashboard')
@section('pageSubtitle', 'Panel po zalogowaniu. Tu będą kluczowe informacje systemowe.')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-ui.card eyebrow="Stan zakładu" title="Pracownicy na terenie">
            <div class="flex items-end gap-3">
                <div class="text-4xl font-semibold text-brandBlueDark">—</div>
                <div class="pb-1 text-sm text-slate-500">w tej chwili</div>
            </div>

            <div class="mt-4 text-sm text-slate-500">
                Dane pojawią się po podpięciu rejestracji RFID i zapisu zdarzeń.
            </div>
        </x-ui.card>

        <x-ui.card eyebrow="Szybkie akcje" title="Najczęstsze operacje">
            <div class="space-y-3">
                <x-ui.button class="w-full">
                    Przejdź do listy pracowników
                </x-ui.button>

                <x-ui.button variant="danger" class="w-full">
                    Drukuj listę ewakuacyjną
                </x-ui.button>
            </div>

            <div class="mt-4 text-xs text-slate-500">
                Akcje podpinamy po dodaniu tras.
            </div>
        </x-ui.card>

        <x-ui.card eyebrow="Status systemu" title="Połączenia">
            <div class="space-y-3">
                <x-ui.dashboard.status-row label="Aplikacja" value="OK" state="ok" />
                <x-ui.dashboard.status-row label="Baza danych" />
                <x-ui.dashboard.status-row label="RFID / Arduino" />
            </div>
        </x-ui.card>
    </div>
@endsection
