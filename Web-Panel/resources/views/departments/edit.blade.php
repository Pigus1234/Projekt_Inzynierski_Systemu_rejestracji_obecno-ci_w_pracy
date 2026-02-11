@extends('layouts.app')

@section('pageTitle', 'Działy')
@section('pageSubtitle', 'Edycja działu.')

@section('content')
    <div class="w-full max-w-none space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Działy" title="Edytuj dział">
            <div class="flex items-center justify-between gap-3">
                <div class="text-sm text-slate-500">
                    Zmień nazwę działu.
                </div>

                <a href="{{ route('departments.index') }}">
                    <x-ui.button variant="secondary" class="px-3 py-2">Wróć</x-ui.button>
                </a>
            </div>

            <form class="mt-6 space-y-5" method="POST" action="{{ route('departments.update', $department) }}">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-900" for="name">Nazwa</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $department->name) }}"
                        required
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-400"
                    />

                    @error('name')
                        <div class="text-sm text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('departments.index') }}">
                        <x-ui.button variant="secondary" class="px-4 py-2">Anuluj</x-ui.button>
                    </a>

                    <x-ui.button type="submit" class="px-4 py-2">
                        Zapisz
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
