@props([
    'leadingIcon' => false,
    'print' => '',
])

@if($print)
<div class="hidden print:block text-sm text-black">{{ __($print) }}</div>
@endif

<div class="flex shadow-sm @if($print) print:hidden @endif">
    @if ($leadingIcon)
        @php $leadingIcon = "icon.".$leadingIcon @endphp
        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-400 sm:text-sm">
            <x-dynamic-component :component="$leadingIcon" />
        </span>
    @endif

    <input {{ $attributes->merge(['class' => 'flex-1 form-input border-gray-300 text-gray-700 block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 ' . ($leadingIcon ? 'rounded-r-md' : 'rounded-md')]) }} type="number" step="1">
</div>
