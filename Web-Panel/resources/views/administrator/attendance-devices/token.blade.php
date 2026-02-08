@extends('layouts.app')

@section('pageTitle', 'Token urządzenia')
@section('pageSubtitle', 'Skopiuj token teraz. Nie będzie już możliwe jego ponowne wyświetlenie.')

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <div class="space-y-4">
                <div class="text-sm text-slate-700">
                    Urządzenie: <span class="font-semibold text-slate-900">{{ $attendanceDevice->name }}</span>
                </div>

                <div class="space-y-2">
                    <div class="text-sm font-medium text-slate-700">
                        Nagłówek wymagany przez API
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-mono text-slate-900">
                        X-Attendance-Device-Token: {{ $plainToken }}
                    </div>
                </div>

                <div class="flex justify-end">
                    <a
                        href="{{ route('administrator.attendance-devices.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-brandBlue px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brandBlueDark focus:outline-none focus:ring-2 focus:ring-brandBlue/20"
                    >
                        Wróć do listy urządzeń
                    </a>
                </div>
            </div>
        </x-ui.card>
    </div>
@endsection
