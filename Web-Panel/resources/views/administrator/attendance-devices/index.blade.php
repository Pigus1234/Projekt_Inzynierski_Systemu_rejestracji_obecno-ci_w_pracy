@extends('layouts.app')

@section('pageTitle', 'Urządzenia odbić')
@section('pageSubtitle', 'Zarządzanie tokenami urządzeń RFID. Token jest pokazywany tylko raz.')

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <div class="flex items-center justify-between gap-4">
                <div class="text-sm text-slate-500">
                    Łącznie: {{ $attendanceDevices->count() }}
                </div>

                <x-ui.button :href="route('administrator.attendance-devices.create')">Dodaj urządzenie</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-ui.table>
                <x-slot:header>
                    <tr class="text-left">
                        <x-ui.table.header-cell>Nazwa</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Status</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Ostatnio widziane</x-ui.table.header-cell>
                        <x-ui.table.header-cell align="right">Akcje</x-ui.table.header-cell>
                    </tr>
                </x-slot:header>

                @forelse($attendanceDevices as $attendanceDevice)
                    <tr>
                        <x-ui.table.data-cell>
                            <div class="text-sm font-semibold text-slate-900">{{ $attendanceDevice->name }}</div>
                        </x-ui.table.data-cell>

                        <x-ui.table.data-cell>
                            @if(!$attendanceDevice->is_active)
                                <x-ui.badge variant="secondary">Nieaktywne</x-ui.badge>
                            @elseif($attendanceDevice->is_online)
                                <x-ui.badge>Online</x-ui.badge>
                            @else
                                <x-ui.badge variant="secondary">Offline</x-ui.badge>
                            @endif
                        </x-ui.table.data-cell>

                        <x-ui.table.data-cell>
                            <div class="text-sm text-slate-700">
                                {{ $attendanceDevice->last_seen_at?->format('Y-m-d H:i:s') ?? '-' }}
                            </div>
                        </x-ui.table.data-cell>

                        <x-ui.table.data-cell align="right">
                            <div class="flex flex-nowrap justify-end gap-2">
                                <form class="w-40" method="GET" action="{{ route('administrator.attendance-devices.edit', $attendanceDevice) }}">
                                    <x-ui.button type="submit" variant="secondary" class="w-full whitespace-nowrap">
                                        Zmień nazwę
                                    </x-ui.button>
                                </form>

                                <form class="w-40" method="POST" action="{{ route('administrator.attendance-devices.rotate-token', $attendanceDevice) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary" class="w-full whitespace-nowrap">
                                        Rotuj token
                                    </x-ui.button>
                                </form>

                                @if($attendanceDevice->is_active)
                                    <form class="w-40" method="POST" action="{{ route('administrator.attendance-devices.deactivate', $attendanceDevice) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="secondary" class="w-full whitespace-nowrap">
                                            Dezaktywuj
                                        </x-ui.button>
                                    </form>
                                @else
                                    <form class="w-40" method="POST" action="{{ route('administrator.attendance-devices.activate', $attendanceDevice) }}">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" class="w-full whitespace-nowrap">
                                            Aktywuj
                                        </x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </x-ui.table.data-cell>

                    </tr>
                @empty
                    <tr>
                        <x-ui.table.data-cell colspan="4">
                            <div class="text-sm text-slate-600">Brak urządzeń.</div>
                        </x-ui.table.data-cell>
                    </tr>
                @endforelse
            </x-ui.table>
        </x-ui.card>
    </div>
@endsection
