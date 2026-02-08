@props([
    'name',
    'label',
    'selectedValue' => null,
    'required' => false,
])

<x-ui.form.select
    :name="$name"
    :label="$label"
    :items="\App\Attendance\AttendanceEventType::selectItems(true)"
    :selectedValue="$selectedValue"
    :required="$required"
/>
