<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'RFID Attendance') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brandBg antialiased text-slate-900">
    <header class="bg-white border-b border-slate-200">
        <div class="h-1 w-full bg-gradient-to-r from-brandBlue via-brandBlueDark to-brandRed"></div>

        <div class="px-6 py-5">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-[1fr_auto] sm:items-start">
                <div class="min-w-0">
                    <h1 class="text-3xl font-semibold text-brandBlueDark">
                        @yield('pageTitle', 'Dashboard')
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        @yield('pageSubtitle', 'Panel po zalogowaniu.')
                    </p>
                </div>

                <div class="flex flex-col items-start space-y-2 sm:items-end">
                    <x-ui.badge>
                        {{ auth()->user()->role?->name ?? 'Brak roli' }}
                    </x-ui.badge>

                    <div class="text-sm font-semibold text-slate-900">
                        {{ auth()->user()->name }}
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="danger">
                            Wyloguj
                        </x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="px-6 py-6">
        @yield('content')
    </main>
</body>
</html>
