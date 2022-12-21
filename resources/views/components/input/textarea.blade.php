@props([
    'leadingIcon' => false,
])

<div class="flex rounded-md shadow-sm">
    @if ($leadingIcon)
        @php $leadingIcon = "icon.".$leadingIcon @endphp
        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-400 sm:text-sm">
            <x-dynamic-component :component="$leadingIcon" />
        </span>
    @endif

    <textarea {{ $attributes->merge(['class' => 'flex-1 form-textarea border-cool-gray-300 text-gray-700 block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5' . ($leadingIcon ? ' rounded-none rounded-r-md' : ''), 'rows' => "3"]) }} ></textarea>
</div>
