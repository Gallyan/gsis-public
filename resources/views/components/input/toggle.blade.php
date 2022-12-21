@props([
    'for' => $attributes['id'],
])

<div class="relative flex items-center h-10 px-3">
    <label class="inline-flex relative items-center cursor-pointer" for="{{ $for }}">
        <input {{ $attributes }} type="checkbox" class="sr-only peer">
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
        <span class="ml-3 text-sm font-medium leading-5 text-gray-700">{{ $slot }}</span>
    </label>
</div>
