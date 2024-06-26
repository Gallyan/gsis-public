<div class="relative">
    <form
        wire:submit.prevent="save"
        wire:reset.prevent="init"
        x-data="{ dirty : @entangle( 'modified' ) }"
        x-init="window.addEventListener('beforeunload', function(e) { if(dirty) { e.preventDefault(); e.returnValue = ''; } });"
    >
        @csrf

        <x-stickytopbar title="{!! __('Purchase Order') !!} {{ $order->id }}" :modified="$modified" :disabled="$disabled" />

@push('scripts')
        <script type="text/javascript">
            Livewire.on('urlChange', param => {
                history.pushState(null, null, param);
            });
        </script>
@endpush

        <div class="mt-6 sm:mt-5">
            @can('manage-users')
            <x-input.group label="User" class="sm:items-center text-cool-gray-600 print:text-black" paddingless borderless>
                <a href="{{ route('edit-user', $order->user) }}" target="_blank" class="hover:underline pr-4">{{ $order->user->name ?? '' }} <sup><x-icon.new-window /></sup></a>
            </x-input.group>
            @endcan

            <x-input.group label="Manager" class="sm:items-center text-cool-gray-600 print:text-black print:text-sm" innerclass="flex items-center" :borderless="!$isAuthManager" :paddingless="!$isAuthManager">
               {{ $order->managers->isNotEmpty() ?
                    $order->managers->map(fn($mgr) => App\Models\User::find($mgr->user_id)->name)->implode(', ') :
                    __('There is no manager yet.') }}
                @can('manage-users')
                <div class="mx-4">
                    @if ( $order->id && $order->status !== 'draft' )
                    @if ( $order->managers->contains('user_id',auth()->id()) )
                        @if ( count($order->managers) > 1 )
                        <x-button.secondary wire:click="dissociate" wire:offline.attr="disabled" wire:loading.attr="disabled">
                            {{ __('Dissociate') }}
                        </x-button.secondary>
                        @else
                        <x-button.secondary wire:click="dissociate" disabled title="{{ __('You are the only manager, you cannot dissociate.') }}">
                            {{ __('Dissociate') }}
                        </x-button.secondary>
                        @endif
                    @else
                    <x-button.primary wire:click="associate" wire:offline.attr="disabled" wire:loading.attr="disabled">
                        {{ __('Associate') }}
                    </x-button.primary>
                    @endif
                    @endif
               </div>
                @endcan
            </x-input.group>

            <x-input.group label="Subject" for="subject" :error="$errors->first('order.subject')" required helpText="helptext-order-subject">
                <x-input.text wire:model.lazy="order.subject" id="subject" :disabled="$disabled" :print="$order->subject"/>
            </x-input.group>

            <x-input.group label="Status" for="status" :error="$errors->first('order.status')" helpText="{!! __('helptext-order-status') !!}" required>
                <x-input.status
                    id="status"
                    wire:model="order.status"
                    :disabled="$disabledStatuses"
                    :selected="$order->status"
                    :keylabel="$order->allStatuses"
                />
            </x-input.group>

            @can ('manage-users')
            <x-input.group label="Amount excl." for="amount" :error="$errors->first('order.amount')" helpText="helptext-amount">
                <x-input.money wire:model.lazy="order.amount" id="amount" :disabled="$disabled" :print="$order->amount" />
            </x-input.group>
            @endcan

            <x-input.group label="Institution" for="institution_id" :error="$errors->first('order.institution_id')" required>
                <x-input.select wire:model="order.institution_id" id="institution_id" placeholder="{{ __('Select Institution...') }}" class="w-full" :disabled="$disabled" :print="$order->institution->namecontract ?? null">

                    <optgroup label="&boxh;&boxh;&boxh;&boxh;&nbsp;{{ __('Available') }}&nbsp;&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;">
                        @foreach (\App\Models\Institution::available() as $ins)
                        <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                        @endforeach
                    </optgroup>

                    <optgroup label="&boxh;&boxh;&boxh;&boxh;&nbsp;{{ __('Unavailable') }}&nbsp;&boxh;&boxh;&boxh;&boxh;&boxh;&boxh;">
                        @foreach (\App\Models\Institution::unavailable() as $ins)
                        <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                        @endforeach
                    </optgroup>

                </x-input.select>

                @if ( $order->institution?->from && $order->institution->from->gt(Illuminate\Support\Carbon::today()) )
                <p class="mt-4 text-red-500 text-sm leading-5">
                    <x-icon.warning class="mr-2 flex-shrink-0 h-6 w-6" />
                    {{ __('Institution will be available from :date', [
                        'date' => $order->institution->from->format('d/m/Y'),
                        ]) }}
                </p>
                @endif

                @if ( $order->institution?->to && $order->institution->to->lt(Illuminate\Support\Carbon::today()) )
                <p class="scr:mt-4 text-red-500 text-sm leading-5">
                    <x-icon.warning class="mr-2 flex-shrink-0 h-6 w-6" />
                    {{ __('Institution was available until :date', [
                        'date' => $order->institution->to->format('d/m/Y'),
                        ]) }}
                </p>
                @endif
            </x-input.group>

            <x-input.group label="Supplier" for="supplier" :error="$errors->first('order.supplier')">
                <x-input.text wire:model.lazy="order.supplier" id="supplier" :disabled="$disabled" :print="$order->supplier"/>
            </x-input.group>

            @php
                $upload_errors = collect( $errors->get('uploads.*') )->map( function( $item, $key ) {
                    return str_replace(
                        ':filename',
                        App\Models\Document::filter_filename(
                            $this->uploads[ intval( preg_filter( '/uploads\./', '', $key ) ) ]
                            ->getClientOriginalName()
                        ),
                        $item
                    );
                });
            @endphp
            <x-input.group label="Quotations" for="uploads" :error="$upload_errors->all()" helpText="helptext-order-quotation">
                <x-input.filepond
                    wire:model="uploads"
                    id="uploads"
                    inputname="uploads[]"
                    multiple
                    maxFileSize="10MB"
                    acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                    :disabled="$disabled"
                />

                @if (!empty($order->documents))
                <ul role="list">
                @foreach( $order->documents as $document )
                    <li class="flex text-gray-500 border-dashed border-2 border-gray-300 rounded-md p-2 my-2 items-center @if ( in_array( $document->id, $del_docs ) ) line-through italic @endif">
                        <x-icon.document class="w-10 h-10 text-gray-500" />
                        <div class="mx-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <a href="{{ route( 'download', $document->id ) }}" class="hover:underline">{{ $document->name }}</a> <span class="text-sm text-gray-500">({{ $document->sizeForHumans }}) {{ __('Added :date',[ 'date' => $document->created_at->diffForHumans() ]) }}</span>
                            </p>
                            <p class="text-sm text-gray-500">{{ __($document->type) }}</p>
                        </div>
                        @if ( !in_array( $document->id, $del_docs ) && !$disabled)
                        <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer print:hidden" wire:click="del_doc({{ $document->id }})"/>
                        @endif
                    </li>
                @endforeach
                </ul>
                @endif

                @if (count($order->documents)>1)
                <p class="text-sm font-medium text-gray-700">
                    <a href="{{ route( 'zip-order', $order ) }}" class="hover:underline">
                        {{ __('Download all files in one zip') }}
                        <x-icon.download class="text-gray-500"/>
                    </a>
                </p>
                @endif

            </x-input.group>

            <x-input.group label="Books" for="books" wire:model="order.books" :error="$errors->first('order.books')">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading small class="max-w-64">{{ __('Title') }}</x-table.heading>
                        <x-table.heading small>{{ __('Author') }}</x-table.heading>
                        <x-table.heading small>{{ __('ISBN') }}</x-table.heading>
                        <x-table.heading small>{{ __('Edition') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6 print:hidden"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse ($order->books as $book)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $book['title'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $book['author'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-center cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $book['isbn'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-nowrap text-center cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ isset( $book['edition'] ) ? ucfirst(__($book['edition'])) : '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center print:hidden">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_book({{ $loop->iteration }})" class="text-cool-gray-600"  title="{{ __('Delete') }}">
                                        <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                    </x-button.link>
                                </span>
                            </x-table.cell>
                            @endif
                        </x-table.row>
                        @empty
                        <x-table.row>
                            <x-table.cell colspan="5">
                                <div class="flex justify-center items-center space-x-2">
                                    <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No books...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="$set('showModal', true)" class="mt-4" :disabled="$disabled"><x-icon.plus/> {{ __('Add book') }}</x-button.secondary>
                @endif

            </x-input.group>

            <x-input.group label="Comments" for="comments" :error="$errors->first('order.comments')">
                <x-input.contenteditable wire:model="order.comments" id="comments" leadingIcon="chat" :content="$order->comments" :disabled="$disabled" />
            </x-input.group>
        </div>
    </form>

    <livewire:messagerie key="msg" :object="$order" />

    <!-- Add book Modal -->
    <form wire:submit.prevent="add_book">
        @csrf

        <x-modal.dialog wire:model.defer="showModal">
            <x-slot name="title">@if(isset($this->book_id)) @lang('Edit book') @else @lang('Add book') @endif</x-slot>

            <x-slot name="content">
                <x-input.group paddingless borderless class="sm:py-1" for="title" label="Title" :error="$errors->first('title')" required>
                    <x-input.text wire:model.debounce.500ms="title" id="title" placeholder="{{ __('Title') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="author" label="Author" :error="$errors->first('author')" required>
                    <x-input.text wire:model.debounce.500ms="author" id="author" placeholder="{{ __('Author') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="isbn" label="ISBN" :error="$errors->first('isbn')" required>
                    <x-input.text wire:model.debounce.500ms="isbn" id="isbn" placeholder="ISBN" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="edition" label="Edition" :error="$errors->first('edition')" required>
                    <x-input.radio id="edition" wire:model="edition" :keylabel="$order->allEditions" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_modal">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@if(isset($this->book_id)) @lang('Update') @else @lang('Add') @endif</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

    <!-- Confirm state change //-->
    <x-modal.information wire:model.defer="showInformationMessage">
        <x-slot name="title">
            {{ __('Information') }}
        </x-slot>

        <x-slot name="content">

        <x-input.group>
            <span class="text-cool-gray-900">
                {{ __( $showInformationMessage ) }}
            </span>
        </x-input.group>

        </x-slot>

        <x-slot name="footer">
            <x-button.primary wire:click="$toggle('showInformationMessage')">{{ __('Ok') }}</x-button.primary>
        </x-slot>
    </x-modal.information>

</div>