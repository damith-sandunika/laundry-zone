@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-2.5 mt-1 text-sm font-semibold text-blue-700 bg-blue-50 rounded-lg transition-all duration-200'
            : 'flex items-center px-4 py-2.5 mt-1 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 group';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>

