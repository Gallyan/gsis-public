@props([
    'for',
])

<div class="relative flex items-center h-10 px-3">
    <div class="flex items-center h-5">
        <input {{ $attributes }}
            type="checkbox"
            class="form-checkbox focus:ring-cyan-600 h-4 w-4 text-cyan-600 border-gray-400 rounded transition duration-150 ease-in-out sm:text-sm sm:leading-5"
        />
    </div>
    <div class="ml-3 text-sm">
      <label for="{{ $for }}" class="block text-sm font-medium leading-5 text-gray-700">{{ $slot }}</label>
    </div>
</div>
