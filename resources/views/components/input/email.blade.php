@props([
    'verified' => null,
    'disabled' => false
])

@php
    $classes = 'flex-1 form-input border-cool-gray-300 block w-full transition duration-150 ease-in-out text-gray-700 sm:text-sm sm:leading-5 rounded-none';
    if ( is_null($verified) )
        $classes .= ' rounded-r-md';
    if ( $disabled )
        $classes .= ' bg-gray-100 cursor-not-allowed';
@endphp

<div class="flex rounded-md shadow-sm">
    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 sm:text-sm">
        <x-icon.email class="h-5 w-5 text-gray-400" />
    </span>

    <input {{ $attributes->merge(['class' => $classes]) }} @disabled($disabled) />

    @if ( $verified === true )
    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-green-400 sm:text-sm" title="{{ __('Verified email') }}">
        <x-icon.check class="h-5 w-5" />
    </span>
    @elseif ( $verified === false )
    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-red-400 sm:text-sm" title="{{ __('Unverified email') }}">
        <x-icon.x class="h-5 w-5" />
    </span>
    @endif
</div>
