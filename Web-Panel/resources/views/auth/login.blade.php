@extends('layouts.guest')

@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <x-ui.auth.header />

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

                <x-ui.form.checkbox name="remember" label="Zapamiętaj mnie" />

                <x-ui.button type="submit" class="w-full">
                    Zaloguj się
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>
@endsection
