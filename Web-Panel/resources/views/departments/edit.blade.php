@extends('layouts.app')

@section('pageTitle', 'Działy')
@section('pageSubtitle', 'Edycja działu.')

@section('content')
    <div class="w-full max-w-none space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Działy" title="Edytuj dział">
            <x-ui.section-actions
                description="Zmień nazwę działu."
                :actionUrl="route('departments.index')"
                actionLabel="Wróć"
                actionVariant="secondary"
                actionButtonClass="px-3 py-2"
            />

            <form class="mt-6 space-y-5" method="POST" action="{{ route('departments.update', $department) }}">
                @csrf
                @method('PUT')

                <x-ui.form.text-input
                    name="name"
                    label="Nazwa"
                    required="true"
                    :value="old('name', $department->name)"
                />

                <x-ui.form.actions :cancelUrl="route('departments.index')" cancelVariant="secondary" class="gap-2 pt-2" />
            </form>
        </x-ui.card>
    </div>
@endsection
