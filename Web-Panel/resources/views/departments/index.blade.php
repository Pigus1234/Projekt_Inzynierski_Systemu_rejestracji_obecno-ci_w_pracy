@extends('layouts.app')

@section('pageTitle', 'Działy')
@section('pageSubtitle', 'Zarządzanie działami.')

@section('content')
    <div class="w-full max-w-none space-y-6">
        <x-ui.flash-messages />

        <x-ui.card eyebrow="Działy" title="Lista działów">
            <x-ui.section-actions description="Wszystkie działy w systemie.">
                @can('departments.manage')
                    <x-slot:actions>
                        <x-ui.button :href="route('departments.create')">Dodaj dział</x-ui.button>
                    </x-slot:actions>
                @endcan
            </x-ui.section-actions>


            <x-ui.table>
                <x-slot:header>
                    <tr class="text-left">
                        <x-ui.table.header-cell>Nazwa</x-ui.table.header-cell>
                        <x-ui.table.header-cell align="right">Akcje</x-ui.table.header-cell>
                    </tr>
                </x-slot:header>

                @forelse($departments as $department)
                    <tr>
                        <x-ui.table.data-cell>
                            <div class="text-sm font-semibold text-slate-900">{{ $department->name }}</div>
                        </x-ui.table.data-cell>

                        <x-ui.table.data-cell align="right">
                            <div class="flex flex-nowrap justify-end gap-2">
                                @can('departments.manage')
                                    <x-ui.button :href="route('departments.edit', $department)" variant="secondary" class="w-32 whitespace-nowrap">
                                        Edytuj
                                    </x-ui.button>
                                @endcan

                                @can('departments.manage')
                                    <form class="w-32" method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Usunąć dział?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" class="w-full whitespace-nowrap">
                                            Usuń
                                        </x-ui.button>
                                    </form>
                                @endcan
                            </div>
                        </x-ui.table.data-cell>
                    </tr>
                @empty
                    <x-ui.table.empty-row colspan="2" message="Brak działów." />
                @endforelse
            </x-ui.table>

            <div class="mt-5">
                {{ $departments->links() }}
            </div>
        </x-ui.card>
    </div>
@endsection
