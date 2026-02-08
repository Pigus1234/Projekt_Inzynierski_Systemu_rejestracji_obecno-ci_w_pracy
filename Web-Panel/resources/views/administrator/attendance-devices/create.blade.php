@extends('layouts.app')

@section('pageTitle', 'Dodaj urządzenie')
@section('pageSubtitle', 'Po utworzeniu token zostanie wyświetlony tylko raz.')

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ route('administrator.attendance-devices.store') }}" class="space-y-5">
                @csrf

                <x-ui.form.text-input
                    name="name"
                    label="Nazwa urządzenia"
                    required="true"
                />

                <x-ui.form.checkbox name="isActive" label="Aktywne" />

                <div class="flex justify-end gap-2">
                    <a
                        href="{{ route('administrator.attendance-devices.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-brandBlueDark transition hover:border-brandBlue focus:outline-none focus:ring-2 focus:ring-brandBlue/20"
                    >
                        Anuluj
                    </a>

                    <x-ui.button type="submit">
                        Utwórz i pokaż token
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
