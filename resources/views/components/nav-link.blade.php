@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold leading-5 text-indigo-700 focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium leading-5 text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
