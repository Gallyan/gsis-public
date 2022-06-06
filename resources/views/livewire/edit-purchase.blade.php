<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="{{ __('Non-mission purchase') }} {{ $purchase->id }}" :modified="$modified" :disabled="$disabled" />

        <div class="mt-6 sm:mt-5">
            <x-input.group label="User" class="sm:items-center text-cool-gray-600 sm:pb-5" paddingless borderless>
                <a href="{{ route('edit-user', $purchase->user) }}" target="_blank">{{ $purchase->user->name ?? '' }}</a>
            </x-input.group>

            <x-input.group label="Manager" class="sm:items-center text-cool-gray-600 sm:pb-5" innerclass="flex items-center">
               {{ $purchase->managers->isNotEmpty() ?
                    $purchase->managers->map(fn($mgr) => App\Models\User::find($mgr->user_id)->name)->implode(', ') :
                    __('There is no manager yet.') }}
                @can('manage-users')
                <div class="mx-4">
                    @if ( $purchase->id && $purchase->status !== 'draft' )
                    @if ( $purchase->managers->contains('user_id',auth()->id()) )
                        @if ( count($purchase->managers) > 1 )
                        <x-button.secondary wire:click="dissociate" wire:offline.attr="disabled">
                            {{ __('Dissociate') }}
                        </x-button.secondary>
                        @else
                        <x-button.secondary wire:click="dissociate" disabled title="{{ __('You are the only manager, you cannot dissociate.') }}">
                            {{ __('Dissociate') }}
                        </x-button.secondary>
                        @endif
                    @else
                    <x-button.primary wire:click="associate" wire:offline.attr="disabled">
                        {{ __('Associate') }}
                    </x-button.primary>
                    @endif
                    @endif
               </div>
                @endcan
            </x-input.group>

            <x-input.group label="Subject" for="subject" :error="$errors->first('purchase.subject')" required>
                <x-input.text wire:model.debounce.500ms="purchase.subject" id="subject" leading-add-on="" :disabled="$disabled" />
            </x-input.group>

            <x-input.group label="Status" for="status" :error="$errors->first('purchase.status')" helpText="{!! __('helptext-purchase-status') !!}" required>
                <x-input.status
                    id="status"
                    wire:model="purchase.status"
                    :disabled="$disabledStatuses"
                    :selected="$purchase->status"
                    :keylabel="$purchase->allStatuses"
                />
            </x-input.group>

            <x-input.group label="Institution" for="institution_id" :error="$errors->first('purchase.institution_id')" required>
                <x-input.select wire:model="purchase.institution_id" id="institution_id" placeholder="{{ __('Select Institution...') }}" class="w-full" :disabled="$disabled">
                    @foreach (\App\Models\Institution::all()->sortBy('name') as $ins)
                    <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>

            <x-input.group label="WP" for="wp" :error="$errors->first('purchase.wp')" required>
                <x-input.select wire:model="purchase.wp" id="wp" placeholder="{{ __('Select WP...') }}" class="w-full" :disabled="$disabled">
                    @foreach (\App\Models\Purchase::WP as $k=>$v)
                    <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>

            <x-input.group label="Non-mission purchases" for="miscs" wire:model="purchase.miscs" :error="$errors->first('purchase.miscs')" helpText="helptext-purchase-misc">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading small class="max-w-64">{{ __('Object') }}</x-table.heading>
                        <x-table.heading small>{{ __('Supplier') }}</x-table.heading>
                        <x-table.heading small>{{ __('Date') }}</x-table.heading>
                        <x-table.heading small>{{ __('Amount') }}</x-table.heading>
                        <x-table.heading small>{{ __('Currency') }}</x-table.heading>
                        <x-table.heading small></x-table.heading>
                    </x-slot>

                    <x-slot name="body">
                        @forelse ($purchase->miscs as $misc)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal cursor-pointer" wire:click="edit_misc({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $misc['subject'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="cursor-pointer" wire:click="edit_misc({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ $misc['supplier'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="text-center cursor-pointer" wire:click="edit_misc({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ $misc['date'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="text-center cursor-pointer" wire:click="edit_misc({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ $misc['miscamount'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>
                            <x-table.cell class="text-center cursor-pointer" wire:click="edit_misc({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ $misc['currency'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="text-center">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_misc({{ $loop->iteration }})" class="text-cool-gray-600"  title="{{ __('Delete') }}">
                                        <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                    </x-button.link>
                                </span>
                            </x-table.cell>
                        </x-table.row>
                        @empty
                        <x-table.row>
                            <x-table.cell colspan="6">
                                <div class="flex justify-center items-center space-x-2">
                                    <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No purchases...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                <x-button.secondary wire:click="$set('showModal', true)" class="mt-4" :disabled="$disabled"><x-icon.plus/> {{ __('Add purchase') }}</x-button.primary>

            </x-input.group>

            <x-input.group label="Receptions" wire:model="purchase.receptions" >
                @forelse ($purchase_receptions as $reception)
                <div @if (!$loop->last) class="pb-6" @endif wire:loading.class="opacity-50" wire:key="row-{{ $loop->iteration }}">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading colspan="2">
                            <span class="inline-flex text-md leading-5 pr-4">
                                {{ __('Reception :id', ['id'=>$loop->iteration]) }}
                            </span>
                            <span class="inline-flex text-sm leading-5 pr-4">
                                <x-button.link wire:click="edit_reception({{ $loop->index }})" wire:loading.attr="disabled" title="{{ __('Edit') }}">
                                    <x-icon.pencil class="h-4 w-4 text-cool-gray-400" />
                                </x-button.link>
                            </span>
                            <span class="inline-flex text-sm leading-5 pr-4">
                                <x-button.link wire:click="del_reception({{ $loop->index }})" wire:loading.attr="disabled" title="{{ __('Delete') }}">
                                    <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                </x-button.link>
                            </span>
                        </x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        <x-table.row>
                            <x-table.cell class="w-32">{{ __('Subject') }}&nbsp;:</x-table.cell>
                            <x-table.cell>{{ $reception['subject'] ?? '' }}</x-table.cell>
                        </x-table.row>
                        <x-table.row class="bg-gray-50">
                            <x-table.cell class="w-32">{{ __('No. of participants') }}&nbsp;:</x-table.cell>
                            <x-table.cell>{{ $reception['number'] ?? '' }}</x-table.cell>
                        </x-table.row>
                    </x-slot>
                </x-table>
                </div>
                @empty
                <div class="pb-6">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading>
                            <span class="inline-flex text-md leading-5 pr-4">
                                {{ __('Reception') }}
                            </span>
                        </x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        <x-table.row>
                            <x-table.cell>
                                <div class="flex justify-center items-center space-x-2">
                                    <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No receptions...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                    </x-slot>
                </x-table>
                </div>
                @endforelse

                <x-button.secondary wire:click="$set('showReception', true)" class="mt-4" :disabled="$disabled"><x-icon.plus/> {{ __('Add reception') }}</x-button.primary>

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
            <x-input.group label="Documents" for="uploads" :error="$upload_errors->all()" >
                <x-input.filepond
                    wire:model="uploads"
                    id="uploads"
                    inputname="uploads[]"
                    multiple
                    maxFileSize="10MB"
                    acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                    :disabled="$disabled"
                />

                @if (!empty($purchase->documents))
                <ul role="list" class="divide-y divide-gray-200">
                @foreach( $purchase->documents as $document )
                    <li class="py-4 flex text-gray-500 @if ( in_array( $document->id, $del_docs ) ) line-through italic @endif">
                        <x-icon.document class="w-10 h-10 text-gray-500" />
                        <div class="mx-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <a href="{{ route( 'download', $document->id ) }}" target="_blank">{{ $document->name }}</a> <span class="text-sm text-gray-500">({{ $document->sizeForHumans }}) {{ __('Added :date',[ 'date' => $document->created_at->diffForHumans() ]) }}</span>
                            </p>
                            <p class="text-sm text-gray-500">{{ __($document->type) }}</p>
                        </div>
                        @if ( !in_array( $document->id, $del_docs ) )
                        <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer" wire:click="del_doc({{ $document->id }})"/>
                        @endif
                    </li>
                @endforeach
                </ul>
                @endif

            </x-input.group>

            @can ('manage-users')
            <x-input.group label="Amount excl." for="amount" :error="$errors->first('purchase.amount')" helpText="helptext-amount">
                <x-input.money wire:model.debounce.500ms="purchase.amount" id="amount" :disabled="$disabled" />
            </x-input.group>
            @endcan

            <x-input.group label="Comments" for="comments" :error="$errors->first('purchase.comments')">
                <x-input.textarea wire:model.lazy="purchase.comments" id="comments" rows="5" class="text-gray-700" :disabled="$disabled" />
            </x-input.group>
        </div>
    </form>

    <livewire:messagerie :object="$purchase" />

    <!-- Add misc Modal -->
    <form wire:submit.prevent="add_misc">
        @csrf

        <x-modal.dialog wire:model.defer="showModal">
            <x-slot name="title">@if(isset($this->misc_id)) @lang('Edit misc') @else @lang('Add misc') @endif</x-slot>

            <x-slot name="content">
                <x-input.group for="subject" label="Object" :error="$errors->first('subject')" required>
                    <x-input.text wire:model.debounce.500ms="subject" id="subject" placeholder="{{ __('Object') }}" />
                </x-input.group>

                <x-input.group for="supplier" label="Supplier" :error="$errors->first('supplier')" required>
                    <x-input.text wire:model.debounce.500ms="supplier" id="supplier" placeholder="{{ __('Supplier') }}" />
                </x-input.group>

                <x-input.group for="date" label="Date" :error="$errors->first('date')" required>
                    <x-input.date wire:model="date" id="date" placeholder="{{ __('YYYY-MM-DD') }}" />
                </x-input.group>

                <x-input.group for="miscamount" label="Amount" :error="$errors->first('miscamount')" required>
                    <x-input.money wire:model.debounce.500ms="miscamount" id="miscamount" :leadingIcon="false"/>
                </x-input.group>

                <x-input.group for="currency" label="Currency" :error="$errors->first('currency')" required>
                    <x-input.text wire:model.debounce.500ms="currency" id="currency" placeholder="{{ __('Currency') }}" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_modal">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@if(isset($this->misc_id)) @lang('Update') @else @lang('Add') @endif</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

    <!-- Add Reception Modal -->
    <form wire:submit.prevent="add_reception">
        @csrf

        <x-modal.dialog wire:model.defer="showReception">
            <x-slot name="title">@if(isset($this->rcpt_index)) @lang('Edit reception') @else @lang('Add reception') @endif</x-slot>

            <x-slot name="content">
                <x-input.group for="rcpt_subject" label="Object" :error="$errors->first('rcpt_subject')" required>
                    <x-input.text wire:model.debounce.500ms="rcpt_subject" id="rcpt_subject" placeholder="{{ __('Object') }}" />
                </x-input.group>

                <x-input.group for="rcpt_number" label="No. of participants" :error="$errors->first('rcpt_number')" required>
                    <x-input.text wire:model.debounce.500ms="rcpt_number" id="rcpt_number" placeholder="{{ __('No. of participants') }}" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_reception">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@if(isset($this->rcpt_index)) @lang('Update') @else @lang('Add') @endif</x-button.primary>
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
            <x-button.primary wire:click="$toggle('showInformationMessage')">{{ __('Ok') }}</x-button.secondary>
        </x-slot>
    </x-modal.information>

</div>


@pushOnce('stylesheets')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endPushOnce

@pushOnce('scripts')
    <script src="https://unpkg.com/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
@endPushOnce