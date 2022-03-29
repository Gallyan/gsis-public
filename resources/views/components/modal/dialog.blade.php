@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg flex">
            {{ $title }}
            <x-notify-message event='dialog-saved' color='text-green-600 flex-1 justify-end'>
                {{ __('Saved!') }}
            </x-notify-message>
            <x-notify-message event='dialog-error' color='text-red-600 flex-1 justify-end'>
                {{ __('Error!') }}
            </x-notify-message>
        </div>

        <div class="mt-4">
            {{ $content }}
        </div>
    </div>

    <div class="px-6 py-4 text-right">
        {{ $footer }}
    </div>
</x-modal>
