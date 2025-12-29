@props([
    'tableClass' => 'min-w-full bg-white',
    'bodyClass' => 'divide-y divide-slate-200',
])

<div {{ $attributes->merge(['class' => 'mt-5 overflow-hidden rounded-xl border border-slate-200']) }}>
    <table class="{{ $tableClass }}">
        @isset($header)
            <thead class="bg-slate-50">
                {{ $header }}
            </thead>
        @endisset

        <tbody class="{{ $bodyClass }}">
            {{ $slot }}
        </tbody>
    </table>
</div>
