@props([
  'content'  => '',
  'disabled' => false,
  'leadingIcon' => false,
])

<div
  class="flex rounded-md shadow-sm mb-4 min-h-24 @if( $disabled ) bg-gray-300 print:bg-white opacity-75 cursor-not-allowed @endif @if(!$content) print:hidden @endif"
  x-data="{ content: @entangle( $attributes->whereStartsWith('wire:model')->first() ) }" >
    @if ($leadingIcon)
        @php $leadingIcon = "icon.".$leadingIcon @endphp
        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-400 sm:text-sm">
            <x-dynamic-component :component="$leadingIcon" />
        </span>
    @endif
    <div
      x-on:blur="content = $event.target.innerText"
      {{ $attributes->only(['class'])->merge(['class' => 'form-textarea block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 text-gray-700' . ($leadingIcon ? ' rounded-none rounded-r-md' : '')]) }}
      contenteditable="{{ $disabled ? "false" : "true" }}" >
      {!! nl2br(e($content)) !!}
    </div>
</div>
