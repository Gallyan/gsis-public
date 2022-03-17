  @props([
    'keylabel' => [],
])

<div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
  @foreach ($keylabel as $key => $label)
  <div class="flex items-center">
    <input
      class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
      {{ $attributes->except(['id']) }}
      id="{{ $key }}"
      type="radio"
      name="{{ $attributes['id'] }}"
      value="{{ $key }}" />
    <label for="{{ $key }}" class="ml-3 block text-sm font-medium text-gray-700">{{ __($label) }}</label>
  </div>
@endforeach
</div>