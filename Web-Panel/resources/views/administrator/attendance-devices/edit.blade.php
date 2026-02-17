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

                <x-ui.form.actions :cancelUrl="route('administrator.attendance-devices.index')" cancelVariant="secondary" class="gap-2" />
            </form>
        </x-ui.card>
    </div>
@endsection
