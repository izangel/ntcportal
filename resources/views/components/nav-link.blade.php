@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150 bg-gray-700 text-white'
            : 'flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-150 text-gray-300 hover:bg-gray-800 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>