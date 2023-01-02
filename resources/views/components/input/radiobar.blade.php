@props([
    'keylabel' => [],
    'selected' => false,
    'disabled' => false,
])

<div {{ $attributes->only(['class'])->merge(['class' => 'border border-gray-300 rounded-md divide-y divide-x divide-gray-300 lg:flex lg:divide-y-0 w-full']) }} >
  @foreach ($keylabel as $key => $label)
    <div class="relative md:flex-auto md:flex">
      <input
        class="hidden"
        {{ $attributes->except(['id','class']) }}
        id="{{ $attributes['id'] }}-{{ $key }}"
        type="radio"
        name="{{ $attributes['id'] }}"
        value="{{ $key }}"
        @if ( $disabled ) disabled @endif
        />

      <label
        class="group flex items-center justify-center w-full cursor-pointer transition duration-300 ease-in-out
          @if ( $disabled ) bg-gray-300 opacity-75 cursor-not-allowed @endif
          @if ( $selected == $key ) text-indigo-600  hover:text-indigo-800 border-indigo-600
          @else text-gray-500 hover:text-gray-700  border-gray-300 @endif bg-gray-50 hover:bg-gray-100"
        for="{{ $attributes['id'] }}-{{ $key }}" >

        <span class="p-2 flex items-center justify-center">

          <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center border-2 rounded-full text-xs
            @if ( $selected == $key ) border-indigo-600 @else border-gray-200 @endif">
            @if ( $selected == $key )
              <x-icon.check />
            @else
              &nbsp;
            @endif
          </span>

          <span class="ml-2 font-medium text-center text-sm leading-5">
            {!! __($label) !!}
          </span>

        </span>

      </label>

    </div>
  @endforeach
</div>