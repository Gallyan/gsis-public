@props([
    'label' => '',
    'for' => '',
    'error' => false,
    'helpText' => false,
    'inline' => false,
    'paddingless' => false,
    'borderless' => false,
    'required' => false,
    'class' => '',
])

@if($inline)
    <div class="{{ $class }}">
        @if($label)
        <label for="{{ $for }}" class="block text-sm font-medium leading-5 text-gray-700 {{ $required ? 'required' : '' }}">{{ __($label) }}</label>
        @endif

        <div class="mt-1 relative rounded-md">
            {{ $slot }}

            @if ($error)
                <div class="mt-1 text-red-500 text-sm">{{ __($error) }}</div>
            @endif

            @if ($helpText)
                <p class="mt-2 text-sm text-gray-500">{{ __($helpText) }}</p>
            @endif
        </div>
    </div>
@else
    <div class="sm:grid sm:grid-cols-5 sm:gap-4 sm:items-start {{ $borderless ? '' : ' sm:border-t ' }} sm:border-gray-200 {{ $paddingless ? '' : ' sm:py-5 ' }} {{ $class }}">
        @if($label)
        <label for="{{ $for }}" class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-2 {{ $required ? 'required' : '' }}">
            {{ __($label) }}
        </label>
        @endif

        <div class="mt-1 sm:mt-0 sm:col-span-4">
            {{ $slot }}

            @if ($error)
                <div class="mt-1 text-red-500 text-sm">{{ __($error) }}</div>
            @endif

            @if ($helpText)
                <p class="mt-2 text-sm text-gray-500">{{ __($helpText) }}</p>
            @endif
        </div>
    </div>
@endif
