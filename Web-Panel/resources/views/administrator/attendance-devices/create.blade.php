@extends('layouts.app')

@section('pageTitle', 'Dodaj urządzenie')
@section('pageSubtitle', 'Po utworzeniu token zostanie wyświetlony tylko raz.')

@section('content')
    <div class="space-y-6">
        <x-ui.card>
            <form method="POST" action="{{ route('administrator.attendance-devices.store') }}" class="space-y-5">
                @csrf

                <x-ui.form.text-input
                    name="name"
                    label="Nazwa urządzenia"
                    required="true"
                />

                <x-ui.form.checkbox name="isActive" label="Aktywne" />

                <x-ui.form.actions
                    :cancelUrl="route('administrator.attendance-devices.index')"
                    cancelVariant="secondary"
                    submitLabel="Utwórz i pokaż token"
                    class="gap-2"
                />

            </form>
        </x-ui.card>
    </div>
@endsection
