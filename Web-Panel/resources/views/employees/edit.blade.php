@extends('layouts.app')

@section('pageTitle', 'Pracownicy')
@section('pageSubtitle', 'Edycja danych pracownika.')

@section('content')
    <div class="w-full max-w-none space-y-6">
        <x-ui.flash-messages />

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

                <x-ui.form.select-row
                    name="department"
                    label="Dział (opcjonalnie)"
                    :items="$departmentOptions"
                    :selectedValue="$departmentSelectedValue"
                    :hint="$departmentSelectionHint"
                />

                <x-ui.form.actions :cancelUrl="route('employees.index')" cancelVariant="secondary" />
            </form>
        </x-ui.card>
    </div>
@endsection
