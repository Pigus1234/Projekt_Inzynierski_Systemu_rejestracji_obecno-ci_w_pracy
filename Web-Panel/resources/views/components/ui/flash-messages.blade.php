@if(session('success'))
    <x-ui.alert variant="success">
        {{ session('success') }}
    </x-ui.alert>
@endif

@if(session('error'))
    <x-ui.alert variant="error">
        {{ session('error') }}
    </x-ui.alert>
@endif
