@props([
  'content'  => '',
  'disabled' => false,
])

<div
  class="flex rounded-md shadow-sm mb-4 @if( $disabled ) bg-gray-300 opacity-75 cursor-not-allowed @endif"
  x-data="{ content: @entangle( $attributes->whereStartsWith('wire:model')->first() ) }" >
    <div
      x-on:blur="content = $event.target.innerText"
      {{ $attributes->only(['class'])->merge(['class' => "form-textarea block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"]) }}
      class="form-textarea block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 text-g"
      contenteditable="{{ $disabled ? "false" : "true" }}" >
      {!! nl2br(e($content)) !!}
    </div>
</div>
