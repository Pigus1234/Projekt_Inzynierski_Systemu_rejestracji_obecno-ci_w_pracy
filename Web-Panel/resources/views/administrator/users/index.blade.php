@extends('layouts.app')

@section('pageTitle', 'Administrator')
@section('pageSubtitle', 'Zarządzanie kontami i uprawnieniami.')

@section('content')
    <div class="max-w-5xl space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Konta" title="Użytkownicy systemu">
            <x-ui.section-actions
                description="Lista wszystkich kont w systemie."
                :actionUrl="route('administrator.users.create')"
                actionLabel="Dodaj użytkownika"
            />

            <x-ui.table>
                <x-slot:header>
                    <tr class="text-left">
                        <x-ui.table.header-cell>Imię i nazwisko</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Rola</x-ui.table.header-cell>
                        <x-ui.table.header-cell>Uprawnienia</x-ui.table.header-cell>
                        <x-ui.table.header-cell align="right">Akcje</x-ui.table.header-cell>
                    </tr>
                </x-slot:header>

                @foreach($users as $user)
                    <x-administrator.users.table-row :user="$user" />
                @endforeach
            </x-ui.table>

            <div class="mt-5">
                {{ $users->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
