@props([
    'title' => '',
])

<div class="sticky flex flex-row top-0 bg-cool-gray-100 pb-6 border-b border-gray-200 z-50">
    <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>

    <div class="flex-grow">
        <div class="flex flex-col">
            <div class="flex-1 space-x-3 flex justify-end items-center">
                <span x-data="{ open: false }" x-init="
                        @this.on('notify-saved', () => {
                            if (open === false) setTimeout(() => { open = false }, 2500);
                            open = true;
                        })
                    " x-show.transition.out.duration.1000ms="open" style="display: none;" class="inline-flex text-green-600">{{ __('Saved!') }}</span>

                <span x-data="{ open: false }" x-init="
                        @this.on('notify-error', () => {
                            if (open === false) setTimeout(() => { open = false }, 2500);
                            open = true;
                        })
                    " x-show.transition.out.duration.1000ms="open" style="display: none;" class="inline-flex text-red-600">{{ __('Error') }}</span>

                <span class="inline-flex rounded-md shadow-sm">
                    <button type="reset" class="mt-1 py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                        {{ __('Reset') }}
                    </button>
                </span>

                <span class="inline-flex rounded-md shadow-sm">
                    <button type="submit" wire:offline.attr="disabled" class="mt-1 py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo disabled:bg-gray-300 transition duration-150 ease-in-out">
                        {{ __('Save') }}
                    </button>
                </span>
            </div>
            <div wire:offline class="mt-1 text-red-500 text-xs sm:text-sm text-right">
                You are now offline. You can't save modification.
            </div>
        </div>
    </div>
</div>