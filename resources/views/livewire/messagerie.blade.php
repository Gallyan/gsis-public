<div>
    <x-input.group label="Messaging" for="newpost">

        <div class="flow-root">
            <div class="relative pb-8">
                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                <div class="relative flex items-start space-x-3">
                    <div class="relative">
                        <img class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500 truncate ring-1 ring-white" src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div>
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-700 max-w-xl">
                            <x-input.textarea wire:model.lazy="newpost" id="newpost" rows="5" class="text-gray-700"/>
                        </div>
                    </div>
                </div>
            </div>

            <ul role="list">

            @foreach ($posts as $post)

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
    </x-input.group>

</div>
