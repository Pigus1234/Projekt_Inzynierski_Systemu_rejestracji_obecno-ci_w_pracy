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

                <div class="min-w-0 md:col-span-2 flex flex-col gap-2 md:flex-row md:items-center md:gap-5">
                    <label class="block text-sm font-medium text-slate-700 md:w-1/2" for="department">
                        Dział (opcjonalnie)
                    </label>

                    <div class="min-w-0 w-full md:w-1/2 md:ml-auto">
                        <select
                            id="department"
                            name="department"
                            class="mt-2 md:mt-0 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none focus:border-brandBlue focus:ring-2 focus:ring-brandBlue/20"
                        >
                            <option value="">—</option>

                            @foreach($departmentOptions as $departmentOption)
                                <option value="{{ $departmentOption }}" @selected(old('department', $departmentSelectedValue) === $departmentOption)>
                                    {{ $departmentOption }}
                                </option>
                            @endforeach
                        </select>

                        @if($departmentSelectionHint)
                            <div class="mt-2 text-sm text-amber-700">{{ $departmentSelectionHint }}</div>
                        @endif

                        @error('department')
                            <div class="mt-2 text-sm text-brandRed">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <x-ui.form.actions
                    :cancelUrl="route('employees.index')"
                    cancelLabel="Wróć"
                    submitLabel="Zapisz"
                />
            </form>
        </x-ui.card>
    </div>
@endsection
