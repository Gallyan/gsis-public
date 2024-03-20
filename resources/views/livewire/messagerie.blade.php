<div id="messaging" wire:poll.15s.visible>
    <x-input.group label="Messaging" for="newpost">
        @if ( !empty($object->id) )
        <div class="flow-root">
            <div class="relative @if(count($object->posts)) pb-8 @endif">
                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 border-dashed border border-gray-200 print:hidden" aria-hidden="true"></span>
                <div class="relative flex items-start space-x-3 print:hidden">
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

                                @if($showAddFile)
                                <div class="max-w-xl">
                                    @php
                                        $upload_errors = collect( $errors->get('uploads.*') )->map( function( $item, $key ) {
                                            return empty($this->uploads) ? $item : str_replace(
                                                ':filename',
                                                App\Models\Document::filter_filename(
                                                    $this->uploads[ intval( preg_filter( '/uploads\./', '', $key ) ) ]
                                                    ->getClientOriginalName()
                                                ),
                                                $item
                                            );
                                        });
                                    @endphp
                                    <x-input.group for="uploads" :error="$upload_errors->all()" inline class="mt-4">
                                        <x-input.filepond
                                            wire:model="uploads"
                                            id="uploads"
                                            inputname="uploads[]"
                                            eventReset="attachmentReset"
                                            multiple
                                            maxFileSize="10MB"
                                            acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                                        />
                                    </x-input.group>
                                </div>
                                @endif

                                <x-button.secondary class="mt-2" type="submit" wire:offline.attr="disabled">
                                    <span wire:loading.remove.delay.shorter wire:target="save"><x-icon.paperplane class="brightness-200 mr-1" />{{ __('Add Message') }}</span>
                                    <span wire:loading.delay.shorter wire:target="save" class="invisible"><x-icon.paperplane />{{ __('Add Message') }}</span>
                                    <div wire:loading.delay.shorter wire:target="save" class="w-full float-left -mt-6"><x-icon.loading class="mx-auto w-6 h-6"/></div>
                                </x-button.secondary>
                                @if(!$showAddFile)
                                <x-button.secondary class="mt-2" wire:offline.attr="disabled" wire:click="$set('showAddFile', true)" >
                                    <x-icon.paper-clip class="brightness-200 mr-1" />{{ __('Add an attachment to the message') }}
                                </x-button.secondary>
                                @endif

                                <x-notify-message event='notify-sent-ok' color='text-green-600 mt-2 text-sm float-right'>{{ __('An email has just been sent!') }}</x-notify-message>

                                @error('body')
                                    <div class="mt-2 text-red-500 text-sm max-w-xl">
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
                            <img class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500 truncate ring-1 ring-white" src="{{ $post->user->avatarUrl() }}" alt="{{ $post->author }}">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $post->author }}
                                </span>
                                <span class="mt-0.5 text-sm text-gray-500" title="{{ $post->created_at }}">
                                    {{ $post->created_at->diffForHumans() }}
                                </span>
                            </div>

                            @if(! empty($post->body))
                            <div class="mt-2 text-sm text-gray-700 max-w-xl bg-gray-50 print:bg-white rounded-md py-2 px-4 border border-gray-200">
                                <p>{!! nl2br(e($post->body)) !!}</p>
                            </div>
                            @endif

                            @if (!empty($post->documents))
                            <ul role="list" class="max-w-xl">
                            @foreach( $post->documents as $document )
                                <li class="flex text-gray-500 border-dashed border-2 border-gray-300 rounded-md p-2 my-2 items-center">
                                    <x-icon.document class="w-10 h-10 text-gray-500" />
                                    <div class="mx-3 flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            <a href="{{ route( 'download', $document->id ) }}" class="hover:underline">{{ $document->name }}</a> <span class="text-sm text-gray-500">({{ $document->sizeForHumans }})</span>
                                        </p>
                                    </div>
                                    <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer" wire:click="confirm({{ $document->id }})" />
                                </li>
                            @endforeach
                            </ul>
                            @endif

                            @if (count($post->documents)>1)
                            <p class="text-sm font-medium text-gray-700">
                                <a href="{{ route( 'zip-post', $post ) }}" class="hover:underline">
                                    {{ __('Download all files in one zip') }}
                                    <x-icon.download class="text-gray-500"/>
                                </a>
                            </p>
                            @endif

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

    <!-- Confirm file deletion //-->
    <x-modal.confirmation wire:model.defer="showDeleteModal">
    <x-slot name="title">
        {{ __('Delete document') }}
    </x-slot>

    <x-slot name="content">

    <x-input.group>
        <span class="text-cool-gray-900">
            {{ __('Do you really want to delete document') }} <span class="italic font-bold whitespace-nowrap">{{ $delDocName }}</span>&nbsp;?
        </span>
    </x-input.group>

    </x-slot>

    <x-slot name="footer">
        <x-button.secondary wire:click="$set('showDeleteModal',false)">{{ __('Cancel') }}</x-button.secondary>

        <x-button class="bg-red-600 hover:bg-red-500 active:bg-red-700" wire:click="del_doc({{ $showDeleteModal }})">{{ __('Delete') }}</x-button>
    </x-slot>
    </x-modal.dialog>

</div>
