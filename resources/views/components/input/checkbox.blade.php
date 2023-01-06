@props([
    'for' => $attributes['id'],
    'disabled' => false,
])

<div class="relative flex items-center h-5 px-2 @if($disabled) cursor-not-allowed @endif">
    <div class="flex items-center h-5">
        <input {{ $attributes }}
            type="checkbox"
            class="form-checkbox focus:ring-cyan-600 h-4 w-4 text-cyan-600 border-gray-400 rounded transition duration-150 ease-in-out sm:text-sm sm:leading-5 @if($disabled) cursor-not-allowed @endif"
            @disabled($disabled)
        />
    </div>
    <label for="{{ $for }}" class="ml-3 block text-sm font-medium leading-5 text-gray-700 @if($disabled) cursor-not-allowed @endif">{{ $slot }}</label>
</div>
