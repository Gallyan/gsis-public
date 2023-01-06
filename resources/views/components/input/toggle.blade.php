@props([
    'for' => $attributes['id'],
    'before' => '',
    'after' => '',
    'choice' => false,
    'disabled' => false,
])

<div {{ $attributes->only(['class'])->merge(['class'=>"relative flex items-center h-10 text-sm font-medium"]) }}
    x-data="{ choice: @entangle( $attributes->whereStartsWith('wire:model')->first() ) }" >
    @if($before)
        <span class="mr-2 text-sm font-medium leading-5 @if(!$choice) text-blue-700 @else text-gray-700 @endif">
            {{ __($before) }}
        </span>
    @endif
    <label class="inline-flex relative items-center @if(!$disabled) cursor-pointer @else cursor-not-allowed @endif" for="{{ $for }}">
        <input {{ $attributes->except(['class']) }} type="checkbox" class="sr-only peer" @disabled($disabled)>
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
    </label>
    @if($after)
        <span class="ml-2 text-sm font-medium leading-5 @if($choice) text-blue-700 @else text-gray-700 @endif">
            {{ __($after) }}
        </span>
    @endif
</div>
