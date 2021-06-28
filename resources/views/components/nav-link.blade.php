@props([
    'route' => 'dashboard',
    'icon' => 'stop',
])

@php
$classes = 'group flex items-center px-2 py-2 text-base font-medium rounded-md text-indigo-100 hover:bg-indigo-600 focus:outline-none focus:bg-indigo-700 transition ease-in-out duration-1000';
$icon_classes = 'mr-3 flex-shrink-0 h-6 w-6 text-indigo-300';
if ( $route == Route::currentRouteName() ) {
    $classes = 'group flex items-center px-2 py-2 text-base font-medium text-white rounded-md bg-indigo-900 focus:outline-none focus:bg-indigo-700 transition ease-in-out duration-1000';
    $icon_classes = 'mr-3 h-6 w-6 text-indigo-400 group-focus:text-indigo-300 transition ease-in-out duration-1000';
}
@endphp

<a href="{{ route( $route ) }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon === 'stop')
        <x-icon.stop class="{{ $icon_classes }}" />
    @elseif ($icon === 'logout')
       <x-icon.logout class="{{ $icon_classes }}" />
       @elseif ($icon === 'users')
       <x-icon.users class="{{ $icon_classes }}" />
       @elseif ($icon === 'institution')
       <x-icon.institution class="{{ $icon_classes }}" />
    @elseif ($icon === 'dashboard')
       <x-icon.dashboard class="{{ $icon_classes }}" />
    @endif
    {{ $slot }}
</a>