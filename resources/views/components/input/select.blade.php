@props([
    'placeholder' => false,
    'trailingAddOn' => false,
    'leadingAddOn' => false,
    'print' => '',
])

@if ($print)
<div class="hidden print:block text-sm sm:mt-2 text-cool-gray-600 sm:pb-5">{{ __($print) }}</div>
@endif

<div class="flex @if($print) print:hidden @endif">
  @if ($leadingAddOn)
    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
        {{ $leadingAddOn }}
    </span>
  @endif

  <select {{ $attributes->merge(['class' => 'form-select block pl-3 pr-10 py-2 text-base leading-6 border-gray-300 text-gray-700 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5 rounded' . ($trailingAddOn ? ' rounded-r-none' : '').($leadingAddOn ? ' rounded-l-none' : '')]) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    {{ $slot }}
  </select>

  @if ($trailingAddOn)
    {{ $trailingAddOn }}
  @endif
</div>
