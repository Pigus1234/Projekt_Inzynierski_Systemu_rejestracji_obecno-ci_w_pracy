@extends('layouts.app')

@section('pageTitle', 'Pracownicy')
@section('pageSubtitle', 'Tworzenie nowego pracownika.')

@section('content')
    <div class="max-w-3xl space-y-6">
        <x-ui.card eyebrow="Pracownik" title="Dodaj pracownika">
            <form method="POST" action="{{ route('employees.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.form.text-input name="rfid_uid" label="Numer RFID" required="true" />
                    <x-ui.form.text-input name="full_name" label="Imię i nazwisko" required="true" />
                </div>

                <x-ui.form.text-input name="department" label="Dział (opcjonalnie)" />

                <x-ui.form.actions
                    :cancelUrl="route('employees.index')"
                    cancelLabel="Anuluj"
                    submitLabel="Zapisz"
                />
            </form>
        </x-ui.card>
    </div>
@endsection
