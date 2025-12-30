@extends('layouts.app')

@section('pageTitle', 'Pracownicy')
@section('pageSubtitle', 'Edycja danych pracownika.')

@section('content')
    <div class="max-w-3xl space-y-6">
        <x-ui.card eyebrow="Pracownik" title="Edytuj pracownika">
            <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <x-ui.form.text-input
                        name="rfid_uid"
                        label="Numer RFID"
                        required="true"
                        value="{{ $employee->rfid_uid }}"
                    />

                    <x-ui.form.text-input
                        name="full_name"
                        label="Imię i nazwisko"
                        required="true"
                        value="{{ $employee->full_name }}"
                    />
                </div>

                <x-ui.form.text-input
                    name="department"
                    label="Dział (opcjonalnie)"
                    value="{{ $employee->department }}"
                />

                <x-ui.form.actions
                    :cancelUrl="route('employees.index')"
                    cancelLabel="Wróć"
                    submitLabel="Zapisz"
                />
            </form>
        </x-ui.card>
    </div>
@endsection
