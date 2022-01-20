@props([
    'key_label' => \App\Models\Order::STATUSES,
    'disabled_keys' => null,
])

<div class="border border-gray-300 rounded-md divide-y divide-x divide-gray-300 lg:flex lg:divide-y-0">
  @foreach ($key_label as $key => $label)
    <div class="relative md:flex-1 md:flex">
      <input
        class="hidden"
        {{ $attributes->except(['id']) }}
        id="{{ $key }}"
        type="radio"
        name="{{ $attributes['id'] }}"
        value="{{ $key }}"
        @if ( !empty( $disabled_keys ) && is_array( $disabled_keys ) && in_array( $key, $disabled_keys ) ) disabled @endif />

      <label
        class="group flex items-center justify-center w-full cursor-pointer transition duration-300 ease-in-out
          @if ( !empty( $disabled_keys ) && is_array( $disabled_keys ) && in_array( $key, $disabled_keys ) ) bg-gray-300 opacity-75 cursor-not-allowed @endif
          @if ( $this->order->status == $key ) text-indigo-600  hover:text-indigo-800 border-indigo-600
          @else text-gray-500 hover:text-gray-700  border-gray-300 @endif bg-gray-50 hover:bg-gray-100"
        for="{{ $key }}" >

        <span class="px-6 py-2 flex items-center justify-center">

          <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center border-2 @if ( $this->order->status == $key ) border-indigo-600 @else border-gray-200 @endif rounded-full text-xs">
            @if ( $this->order->status === $key )
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