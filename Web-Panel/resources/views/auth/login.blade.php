@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <div class="text-center">
                <div class="text-sm font-semibold text-brandBlueDark">RFID Attendance System</div>
            </div>

            <div class="mt-6">
                <div class="text-2xl font-semibold text-brandBlueDark">Logowanie</div>
                <div class="mt-2 text-sm text-slate-500">Wprowadź dane dostępowe, aby przejść do panelu.</div>
            </div>

            <form class="mt-6 space-y-5" method="POST" action="{{ route('login') }}">
                @csrf

                <x-ui.form.text-input
                    name="email"
                    label="Adres e-mail"
                    type="email"
                    autocomplete="username"
                    required="true"
                />

                <x-ui.form.text-input
                    name="password"
                    label="Hasło"
                    type="password"
                    autocomplete="current-password"
                    required="true"
                />

                <label class="flex items-center gap-3 text-sm text-slate-700">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-brandBlue focus:ring-brandBlue/30">
                    Zapamiętaj mnie
                </label>

                <x-ui.button type="submit" class="w-full">
                    Zaloguj się
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
