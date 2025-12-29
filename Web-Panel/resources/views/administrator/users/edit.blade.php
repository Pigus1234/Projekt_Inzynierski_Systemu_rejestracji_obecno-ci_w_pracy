@extends('layouts.app')

@section('pageTitle', 'Administrator')
@section('pageSubtitle', 'Edycja konta i uprawnień.')

@section('content')
    <div class="max-w-3xl space-y-6">
        <x-ui.card eyebrow="Konto" title="Edytuj użytkownika">
            <form method="POST" action="{{ route('administrator.users.update', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.form.text-input
                        name="name"
                        label="Imię i nazwisko"
                        required="true"
                        value="{{ $user->name }}"
                    />

                    <x-ui.form.text-input
                        name="email"
                        label="Adres e-mail"
                        type="email"
                        autocomplete="username"
                        required="true"
                        value="{{ $user->email }}"
                    />
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.form.text-input
                        name="password"
                        label="Nowe hasło (opcjonalnie)"
                        type="password"
                        autocomplete="new-password"
                    />

                    <x-ui.form.select
                        name="role_id"
                        label="Rola"
                        :items="$roles"
                        valueProperty="id"
                        labelProperty="name"
                        :selectedValue="$user->role_id"
                        required="true"
                    />
                </div>

                <x-ui.form.checkbox-group
                    name="permission_ids"
                    title="Uprawnienia"
                    :items="$permissions"
                    valueProperty="id"
                    labelProperty="label"
                    :selectedValues="$user->permissions->pluck('id')->all()"
                />

                <x-ui.form.actions
                    :cancelUrl="route('administrator.users.index')"
                    cancelLabel="Wróć"
                    submitLabel="Zapisz"
                />
            </form>
        </x-ui.card>
    </div>
@endsection
