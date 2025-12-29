@props([
    'systemName' => 'RFID Attendance System',
    'title' => 'Logowanie',
    'subtitle' => 'Wprowadź dane dostępowe, aby przejść do panelu.',
])

<div>
    <div class="text-center">
        <div class="text-sm font-semibold text-brandBlueDark">{{ $systemName }}</div>
    </div>

    <div class="mt-6">
        <div class="text-2xl font-semibold text-brandBlueDark">{{ $title }}</div>
        <div class="mt-2 text-sm text-slate-500">{{ $subtitle }}</div>
    </div>
</div>
