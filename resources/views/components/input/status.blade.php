@props([
    'keylabel' => [],
    'disabled' => [],
    'selected' => '',
])

@if($selected)
<div class="hidden print:block text-sm text-cool-gray-600 sm:pb-5 sm:mt-2">{{ ucfirst(__($selected)) }}</div>
@endif

<div class="border border-gray-300 rounded-md divide-y divide-x divide-gray-300 lg:flex lg:divide-y-0 @if($selected) print:hidden @endif">
  @foreach ($keylabel as $key => $label)
    <div class="relative md:flex-1 md:flex">
      <input
        class="hidden"
        {{ $attributes->except(['id']) }}
        id="{{ $key }}"
        type="radio"
        name="{{ $attributes['id'] }}"
        value="{{ $key }}"
        @if ( in_array( $key, $disabled ) ) disabled @endif />

      <label
        class="group flex items-center justify-center w-full transition duration-300 ease-in-out
          @if ( in_array( $key, $disabled ) ) bg-gray-300 opacity-75 cursor-not-allowed
          @else
          bg-gray-100 cursor-pointer hover:bg-gray-200 hover:text-gray-700
          @endif
          @if ( $selected == $key ) text-indigo-600  hover:text-indigo-800 border-indigo-600
          @else text-gray-500 border-gray-300 @endif"
        for="{{ $key }}" >

        <span class="px-6 py-2 flex items-center justify-center">

          <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center border-2 rounded-full text-xs
            @if ( $selected == $key ) border-indigo-600 @else border-gray-400 @endif">
            @if ( $selected === $key )
              <x-icon.check />
            @else
              {{ $loop->iteration }}
            @endif
          </span>

          <span class="ml-2 font-medium text-center text-sm leading-5">
            {{ __($label) }}
          </span>

        </span>

      </label>

    </div>
  @endforeach
</div>