@props([
    'user',
])

@php
    $isTargetAdministrator = $user->role?->name === 'Administrator';
    $isActingAdministrator = auth()->user()->role?->name === 'Administrator';
    $canManageThisUser = $isActingAdministrator || ! $isTargetAdministrator;
    $isSelf = $user->id === auth()->id();
@endphp

<tr>
    <x-ui.table.data-cell>
        <div class="text-sm font-semibold text-slate-900">{{ $user->name }}</div>
        <div class="text-sm text-slate-500">{{ $user->email }}</div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell>
        <div class="text-sm text-slate-700">
            {{ $user->role?->name ?? 'Brak roli' }}
        </div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell>
        <div class="text-sm text-slate-700">
            {{ $user->permissions->count() }}
        </div>
    </x-ui.table.data-cell>

    <x-ui.table.data-cell align="right">
        <x-ui.action-group>
            @if($isSelf)
                <div class="text-sm font-semibold text-slate-400">
                    Twoje konto
                </div>
            @elseif($canManageThisUser)
                <x-ui.button :href="route('users.edit', $user)" class="px-3 py-2">Edytuj</x-ui.button>

                <form method="POST" action="{{ route('users.destroy', $user) }}">
                    @csrf
                    @method('DELETE')

                    <x-ui.button type="submit" variant="danger" class="px-3 py-2">
                        Usuń
                    </x-ui.button>
                </form>
            @else
                <div class="text-sm font-semibold text-slate-400">
                    Brak dostępu
                </div>
            @endif
        </x-ui.action-group>
    </x-ui.table.data-cell>
</tr>
