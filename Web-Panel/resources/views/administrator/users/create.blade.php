@extends('layouts.app')

@section('pageTitle', 'Administrator')
@section('pageSubtitle', 'Tworzenie konta użytkownika.')

@section('content')
    <div class="max-w-3xl space-y-6">
        <x-ui.card eyebrow="Konto" title="Dodaj użytkownika">
            <form method="POST" action="{{ route('administrator.users.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.form.text-input name="name" label="Imię i nazwisko" required="true" />
                    <x-ui.form.text-input name="email" label="Adres e-mail" type="email" autocomplete="username" required="true" />
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.form.text-input name="password" label="Hasło" type="password" autocomplete="new-password" required="true" />

                    <x-ui.form.select
                        name="role_id"
                        label="Rola"
                        :items="$roles"
                        valueProperty="id"
                        labelProperty="name"
                        required="true"
                    />
                </div>

                <x-ui.form.checkbox-group
                    name="permission_ids"
                    title="Uprawnienia"
                    :items="$permissions"
                    valueProperty="id"
                    labelProperty="label"
                />

                <x-ui.form.actions
                    :cancelUrl="route('administrator.users.index')"
                    submitLabel="Zapisz"
                />
            </form>
        </x-ui.card>
    </div>
@endsection
