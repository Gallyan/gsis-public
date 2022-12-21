<div id="messaging">
    <x-input.group label="Messaging" for="newpost">
        @if ( !empty($object->id) )
        @push('scripts')
        <script>
        setInterval(function() {
            Livewire.emit('refreshMessages');
        }, 60 * 1000);
        </script>
        @endpush
        <div class="flow-root">
            <div class="relative @if (count($object->posts)) pb-8 @endif">
                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 border-dashed border border-gray-200" aria-hidden="true"></span>
                <div class="relative flex items-start space-x-3">
                    <div class="relative">
                        <img class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500 truncate ring-1 ring-white" src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div>
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-700 max-w-xl">
                            <form wire:submit.prevent="save">
                                @csrf
                                <x-input.textarea wire:model.debounce.500ms="body" id="body" rows="5" leadingIcon="chat" />
                                <x-button.secondary class="mt-2" type="submit" wire:offline.attr="disabled">
                                    <span wire:loading.remove.delay.shorter wire:target="save"><x-icon.paperplane class="brightness-200 mr-1" />{{ __('Add Message') }}</span>
                                    <span wire:loading.delay.shorter wire:target="save" class="invisible"><x-icon.paperplane />{{ __('Add Message') }}</span>
                                    <div wire:loading.delay.shorter wire:target="save" class="w-full float-left -mt-6"><x-icon.loading class="mx-auto w-6 h-6"/></div>
                                </x-button.secondary>

                                <x-notify-message event='notify-sent-ok' color='text-green-600 mt-2 text-sm float-right'>{{ __('An email has just been sent!') }}</x-notify-message>

                                @error('body')
                                    <div class="mt-2 text-red-500 text-sm float-right">
                                        {{ __($errors->first('body')) }}
                                    </div>
                                @enderror
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <ul role="list">

            @foreach ($object->posts as $post)

            <li>
                <div class="relative pb-8">
                    @if (!$loop->last)
                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                    @endif
                    <div class="relative flex items-start space-x-3">
                        <div class="relative">
                            <img class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500 truncate ring-1 ring-white" src="{{ $post->user()->first()->avatarUrl() }}" alt="{{ $post->user()->first()->name }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $post->user()->first()->name }}
                                </span>
                                <span class="mt-0.5 text-sm text-gray-500" title="{{ $post->created_at }}">
                                    {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="mt-2 text-sm text-gray-700 max-w-xl bg-gray-50 rounded-md py-2 px-4 border border-gray-200">
                                <p>{!! nl2br(e($post->body)) !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            @endforeach
            </ul>
        </div>
        @else
        <p class="mt-2 text-sm text-gray-500">
            {{ __('messaging-inactive') }}
        </p>
        @endif
    </x-input.group>

</div>
