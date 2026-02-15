<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Wydruk')</title>

    <style>
        @page { margin: 12mm; }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            color: #000;
            margin: 0;
        }

        .no-print { display: block; }
        @media print { .no-print { display: none !important; } }
    </style>

    @yield('styles')
</head>
<body>
    @yield('content')

    @yield('scripts')
</body>
</html>
