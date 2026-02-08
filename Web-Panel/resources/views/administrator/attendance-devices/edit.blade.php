@extends('layouts.app')

@section('pageTitle', 'Zmień nazwę urządzenia')
@section('pageSubtitle', 'Nazwa jest widoczna w panelu oraz w diagnostyce urządzeń.')

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ route('administrator.attendance-devices.update', $attendanceDevice) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-ui.form.text-input
                    name="name"
                    label="Nazwa urządzenia"
                    required="true"
                    :value="old('name', $attendanceDevice->name)"
                />

                <div class="flex justify-end gap-2">
                    <a href="{{ route('administrator.attendance-devices.index') }}">
                        <x-ui.button variant="secondary">Anuluj</x-ui.button>
                    </a>

                    <x-ui.button type="submit">
                        Zapisz
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
