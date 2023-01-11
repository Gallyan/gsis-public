@props([
    'title' => '',
])

<div class="sticky flex flex-row top-0 bg-cool-gray-100 pb-4 border-b border-gray-200 z-40 print:pb-0 print:bg-white">
    <h1 class="text-lg md:text-2xl font-semibold text-gray-900">{{ __($title) }}</h1>

    <div class="flex-grow hidden print:block">
        <div class="text-sm sm:mt-2 text-cool-gray-600 text-right">
            @lang('Printed on :date',['date'=>date('d/m/Y H:i')])
        </div>
    </div>

    <div class="flex-grow print:hidden">
        <div class="flex flex-col">
            <div class="flex-1 space-x-3 flex justify-end items-center mt-1">

                <x-notify-message event='notify-saved' color='text-green-600'>{{ __('Saved!') }}</x-notify-message>

                <x-notify-message event='notify-error' color='text-red-600'>{{ __('Error!') }}</x-notify-message>

                <div wire:loading.delay><x-icon.loading class="w-6 h-6" /></div>

                <x-button.secondary type="reset">{{ __('Reset') }}</x-button.primary>

                @if ( $attributes['modified'] && ! $attributes['disabled'] )
                    <x-button.primary type="submit" wire:offline.attr="disabled" class="flex items-start">
                        {{ __('Save') }}
                        <span class="animate-ping bg-white w-2 h-2 rounded-full -mr-2"></span>
                    </x-button.primary>
                @else
                    <x-button.primary type="submit" disabled>
                        {{ __('Save') }}
                    </x-button.primary>
                @endif

            </div>
            <div wire:offline class="mt-1 text-red-500 text-xs sm:text-sm text-right">
                You are now offline. You can't save modification.
            </div>
        </div>
    </div>
</div>