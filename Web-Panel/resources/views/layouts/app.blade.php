<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'RFID Attendance') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brandBg antialiased text-slate-900">
    <header class="border-b border-slate-200 bg-white">
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

                    <nav class="mt-4 flex flex-wrap gap-2">
                        <x-ui.nav.link :href="route('dashboard')" activePattern="dashboard">
                            Dashboard
                        </x-ui.nav.link>

                        @can('employees.manage.view')
                            <x-ui.nav.link :href="route('employees.index')" activePattern="employees.*">
                                Pracownicy
                            </x-ui.nav.link>
                        @endcan

                        @can('administrator.panel')
                            <x-ui.nav.link :href="route('administrator.users.index')" activePattern="administrator.*">
                                Administrator
                            </x-ui.nav.link>
                        @endcan

                        @can('departments.manage')
                            <x-ui.nav.link :href="route('departments.index')" activePattern="departments.*">
                                Działy
                            </x-ui.nav.link>
                        @endcan

                        @can('attendance.present.view')
                            <x-ui.nav.link :href="route('attendance.present')" activePattern="attendance.present">
                                Obecni
                            </x-ui.nav.link>
                        @endcan

                        @can('attendance.present.print')
                            <x-ui.nav.link
                                :href="route('attendance.present.print')"
                                activePattern="attendance.present.print"
                                target="_blank"
                                rel="noopener"
                            >
                                Druk listy ewakuacyjnej
                            </x-ui.nav.link>
                        @endcan

                        @can('attendance.changelog.view')
                            <x-ui.nav.link :href="route('attendance.changelog')" activePattern="attendance.changelog">
                                Changelog odbić
                            </x-ui.nav.link>
                        @endcan

                        @can('administrator.only')
                            <x-ui.nav.link :href="route('administrator.attendance-devices.index')" activePattern="administrator.attendance-devices.*">
                                Urządzenia
                            </x-ui.nav.link>
                        @endcan
                    </nav>
                </div>

                <div class="flex flex-col items-start space-y-2 sm:items-end">
                    <x-ui.badge>
                        {{ auth()->user()?->role?->name ?? 'Brak roli' }}
                    </x-ui.badge>

                    <div class="text-sm font-semibold text-slate-900">
                        {{ auth()->user()?->name }}
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
