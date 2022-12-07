<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="{{ __('Mission') }} {{ $mission->id }}" :modified="$modified" :disabled="$disabled" />

@push('scripts')
        <script type="text/javascript">
            Livewire.on('urlChange', param => {
                history.pushState(null, null, param);
            });
        </script>
@endpush

        <div class="mt-6 sm:mt-5">
            @can('manage-users')
            <x-input.group label="User" class="sm:items-center text-cool-gray-600 sm:pb-5" paddingless borderless>
                <a href="{{ route('edit-user', $mission->user) }}" target="_blank" class="hover:underline pr-4">{{ $mission->user->name ?? '' }} <sup><x-icon.new-window /></sup></a>
                <a href="mailto:{{ $mission->user->email }}" class="pr-4"><x-icon.email /> {{ $mission->user->email }}</a>
                <span><x-icon.birthday /> {{ $mission->user->birthday->format('d/m/Y') }}</span>
            </x-input.group>
            @endcan

            <x-input.group label="Manager" class="sm:items-center text-cool-gray-600 sm:pb-5" innerclass="flex items-center" :borderless="!$isAuthManager" :paddingless="!$isAuthManager">
               {{ $mission->managers->isNotEmpty() ?
                    $mission->managers->map(fn($mgr) => App\Models\User::find($mgr->user_id)->name)->implode(', ') :
                    __('There is no manager yet.') }}
                @can('manage-users')
                <div class="mx-4">
                    @if ( $mission->id && $mission->status !== 'draft' )
                    @if ( $mission->managers->contains('user_id',auth()->id()) )
                        @if ( count($mission->managers) > 1 )
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

            <x-input.group label="Purpose of the mission" for="subject" :error="$errors->first('mission.subject')" required>
                <x-input.text wire:model.debounce.500ms="mission.subject" id="subject" leading-add-on="" :disabled="$disabled" />
            </x-input.group>

            <x-input.group label="Status" for="status" :error="$errors->first('mission.status')" helpText="{!! __('helptext-status') !!}" required>
                <x-input.status
                    id="status"
                    wire:model="mission.status"
                    :disabled="$disabledStatuses"
                    :selected="$mission->status"
                    :keylabel="$mission->allStatuses"
                />
            </x-input.group>

            @if ( in_array( $mission->status, ['in-progress','processed','cancelled'] ) )
            <x-input.group label="MO number" for="om" :error="$errors->first('mission.om')">
                <x-input.text wire:model.debounce.500ms="mission.om" id="om" leading-add-on="" :disabled="$disabled" />
            </x-input.group>
            @endif

            <x-input.group label="Institution" for="institution_id" :error="$errors->first('mission.institution_id')" required>
                <x-input.select wire:model="mission.institution_id" id="institution_id" placeholder="{{ __('Select Institution...') }}" class="w-full" :disabled="$disabled">
                    @foreach (\App\Models\Institution::all()->sortBy('name') as $ins)
                    <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                    @endforeach
                </x-input.select>
            </x-input.group>

            <x-input.group label="WP" for="wp" :error="$errors->first('mission.wp')" :required="$showWP">
                @if ($showWP)
                    <x-input.number wire:model="mission.wp" id="wp" min="1" :disabled="$disabled" />
                @else
                    <span class="sm:items-center text-cool-gray-600 sm:pb-5">
                        {{ __('wp-sometimes') }}
                    </span>
                @endif
            </x-input.group>

            <x-input.group label="Conference" for="conference" :error="$errors->first('mission.conference')" helpText="helptext-mission-conference" required>
                <x-input.radiobar
                    id="conference"
                    wire:model="mission.conference"
                    :selected="$mission->conference"
                    :keylabel="['Non','Oui']"
                />
            </x-input.group>

            @if ( $mission->conference )
            @php
                $programme_errors = collect( $errors->get('programme.*') )->map( function( $item, $key ) {
                    return str_replace(
                        ':filename',
                        App\Models\Document::filter_filename(
                            $this->programme[ intval( preg_filter( '/uploads\./', '', $key ) ) ]
                            ->getClientOriginalName()
                        ),
                        $item
                    );
                });
            @endphp
            <x-input.group label="" for="" :error="$programme_errors->all()" borderless innerclass="sm:col-start-2" >
                @if (empty($mission->programme))
                <x-input.filepond
                    wire:model="programme"
                    id="programme"
                    inputname="programme"
                    maxFileSize="10MB"
                    acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                    :disabled="$disabled"
                />
                @else
                <ul role="list" class="divide-y divide-gray-200">
                    <li class="py-4 flex text-gray-500">
                        <x-icon.document class="w-10 h-10 text-gray-500" />
                        <div class="mx-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <a href="{{ route( 'download', $mission->programme->id ) }}" target="_blank">{{ $mission->programme->name }}</a> <span class="text-sm text-gray-500">({{ $mission->programme->sizeForHumans }}) {{ __('Added :date',[ 'date' => $mission->programme->created_at->diffForHumans() ]) }}</span>
                            </p>
                            <p class="text-sm text-gray-500">{{ __($mission->programme->type) }}</p>
                        </div>
                        @if ( !in_array( $mission->programme->id, $del_docs ) )
                        <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer" wire:click="del_doc({{ $mission->programme->id }})"/>
                        @endif
                    </li>
                </ul>
                @endif

                <x-input.group for="conf_amount" label="Montant de l'inscription payé par l'institution" :error="$errors->first('mission.conf_amount')" class="mt-2" inline>
                    <x-input.money wire:model.debounce.500ms="mission.conf_amount" id="conf_amount" :disabled="$disabled" />
                </x-input.group>

                <x-input.group for="conf_currency" label="Currency" :error="$errors->first('mission.conf_currency')" class="mt-2" inline>
                    <x-input.currency wire:model="mission.conf_currency" id="conf_currency" :disabled="$disabled" />
                </x-input.group>

            </x-input.group>
            @endif

            <x-input.group label="Mission with or without costs" for="costs" :error="$errors->first('mission.costs')" required>
                <x-input.radiobar
                    id="costs"
                    wire:model="mission.costs"
                    :selected="$mission->costs"
                    :keylabel="['Sans','Avec']"
                    :disabled="$disabled"
                />
            </x-input.group>

            <x-input.group label="Destination" for="dest_country" :error="$errors->first('mission.dest_country')" required>
                <x-input.country wire:model="mission.dest_country" id="dest_country" placeholder="{{ __('Select Country...') }}" class="w-full" :disabled="$disabled" />
            </x-input.group>

            <x-input.group label="City" for="dest_city" :error="$errors->first('mission.dest_city')" required>
                <x-input.text wire:model.debounce.500ms="mission.dest_city" id="dest_city" leading-add-on="" :disabled="$disabled" />
            </x-input.group>

            <x-input.group label="Departure" for="departure" :error="$errors->first('mission.departure')" required>
                <x-input.date wire:model="mission.departure" id="departure" placeholder="YYYY-MM-DD" required />
                <p class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-2 pb-1">@lang('From your address'):</p>
                <x-input.radiobar
                    id="from"
                    wire:model="mission.from"
                    :selected="$mission->from"
                    :keylabel="['Work address','Home address']"
                />
            </x-input.group>

            <x-input.group label="Return" for="return" :error="$errors->first('mission.return')" required>
                <x-input.date wire:model="mission.return" id="return" placeholder="YYYY-MM-DD" required />
                <p class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-2 pb-1">@lang('To your address'):</p>
                <x-input.radiobar
                    id="to"
                    wire:model="mission.to"
                    :selected="$mission->to"
                    :keylabel="['Work address','Home address']"
                />
            </x-input.group>

            <x-input.group label="Transport Tickets" for="tickets" :error="$errors->first('mission.tickets')">
                <x-input.radiobar
                    id="tickets"
                    wire:model="mission.tickets"
                    :selected="$mission->tickets"
                    :keylabel="['Non','Oui']"
                />
            </x-input.group>

            <x-input.group label="Accomodation" for="accomodation" :error="$errors->first('mission.accomodation')">
                <x-input.radiobar
                    id="accomodation"
                    wire:model="mission.accomodation"
                    :selected="$mission->accomodation"
                    :keylabel="['Non','Oui']"
                />
            </x-input.group>

            <x-input.group label="Expected extra costs" for="extra" :error="$errors->first('mission.extra')">
                @if( ! is_null( $mission->extra ) )
                    @if( isset( $mission->extra['extra_meal'] ) )
                        <p class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-1">
                            @lang('Meal'): {{ $mission->extra['extra_meal'] ? __('Actual costs') : __('Flat-rate costs') }}
                        </p>
                    @endif
                    @php
                        $extra_list = [];
                        foreach( $mission->extra as $key => $extra )
                            if( ! in_array( $key, ['extra_meal','extra_others'] ) && $extra )
                                $extra_list[] = __($key);
                    @endphp
                    @if( ! empty( $extra_list ) )
                        <p class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-1">
                            @lang('Extra'): @php echo implode( ', ', $extra_list ) @endphp
                        </p>
                    @endif
                    @if( !empty($mission->extra['extra_others']) )
                        <p class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-1">
                            @lang('Others'): {!! nl2br(e($mission->extra['extra_others'])) !!}
                        </p>
                    @endif
                @endif

                @if (!$disabled)
                <x-button.secondary wire:click="edit_extra" @class(['mt-4'=>!is_null($mission->extra)]) :disabled="$disabled"><x-icon.pencil/> {{ __('Edit expected extra costs') }}</x-button.primary>
                @endif

                @if ($disabled && is_null( $mission->extra ) )
                    <p class="block text-sm font-medium leading-5 text-gray-700 sm:mt-px pt-1">
                        @lang('No expected extra costs')
                    </p>
                @endif
            </x-input.group>

            <x-input.group label="Comments" for="comments" :error="$errors->first('mission.comments')">
                <x-input.contenteditable wire:model="mission.comments" id="comments" :content="$mission->comments" class="text-gray-700" :disabled="$disabled" />
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
            <x-input.group label="Send your documents" for="uploads" :error="$upload_errors->all()" >
                <x-input.filepond
                    wire:model="uploads"
                    id="uploads"
                    inputname="uploads[]"
                    multiple
                    maxFileSize="10MB"
                    acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                    :disabled="$disabled"
                />

                @if (!empty($mission->documents))
                <ul role="list" class="divide-y divide-gray-200">
                @foreach( $mission->documents as $document )
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

{{--
            <x-input.group label="Books" for="books" wire:model="mission.books" :error="$errors->first('mission.books')">
                <x-table>
                    <x-slot name="head">
                        <x-table.heading small class="max-w-64">{{ __('Title') }}</x-table.heading>
                        <x-table.heading small>{{ __('Author') }}</x-table.heading>
                        <x-table.heading small>{{ __('ISBN') }}</x-table.heading>
                        <x-table.heading small>{{ __('Edition') }}</x-table.heading>
                        <x-table.heading small class="w-6"></x-table.heading>
                    </x-slot>

                    <x-slot name="body">
                        @forelse ($mission->books as $book)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $book['title'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ $book['author'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="text-center cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ $book['isbn'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="text-center cursor-pointer" wire:click="edit_book({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                    <p class="text-cool-gray-600 truncate">
                                        {{ isset( $book['edition'] ) ? ucfirst(__($book['edition'])) : '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="text-center">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_book({{ $loop->iteration }})" class="text-cool-gray-600"  title="{{ __('Delete') }}">
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
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No books...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="$set('showModal', true)" class="mt-4" :disabled="$disabled"><x-icon.plus/> {{ __('Add book') }}</x-button.primary>
                @endif

            </x-input.group>
--}}
{{--
            @can ('manage-users')
            <x-input.group label="Amount excl." for="amount" :error="$errors->first('mission.amount')" helpText="helptext-amount">
                <x-input.money wire:model.debounce.500ms="mission.amount" id="amount" :disabled="$disabled" />
            </x-input.group>
            @endcan
--}}
        </div>

    </form>

    <livewire:messagerie :object="$mission" />

    <!-- Edit extra Modal -->
    <form wire:submit.prevent="save_extra">
        @csrf

        <x-modal.dialog wire:model.defer="showExtra">
            <x-slot name="title">@lang('Edit expected extra costs')</x-slot>

            <x-slot name="content">
                <x-input.group label="Meal" for="extra_meal" :error="$errors->first('extra_meal')">
                    <x-input.radiobar
                        id="extra_meal"
                        wire:model="extra_meal"
                        :selected="$extra_meal"
                        :keylabel="['Flat-rate costs','Actual costs']"
                    />
                </x-input.group>

                <x-input.group label="Extra">
                    <x-input.group :error="$errors->first('extra_taxi')" inline>
                        <x-input.checkbox wire:model="extra_taxi" id="extra_taxi" for="extra_taxi">
                            {{ __('Taxi') }}
                        </x-input.checkbox>
                    </x-input.group>
                    <x-input.group :error="$errors->first('extra_transport')" inline>
                        <x-input.checkbox wire:model="extra_transport" id="extra_transport" for="extra_transport">
                            {{ __('Public transport') }}
                        </x-input.checkbox>
                    </x-input.group>
                    <x-input.group :error="$errors->first('extra_personal_car')" inline>
                        <x-input.checkbox wire:model="extra_personal_car" id="extra_personal_car" for="extra_personal_car">
                            {{ __('Private car') }}
                        </x-input.checkbox>

                        @if ( $extra_personal_car )
                            @php
                                $missing_doc[] = __( 'car-registration' );
                                $missing_doc[] = __( 'insurance' );
                                if ( auth()->user()
                                           ->load( ['documents' => fn ($query) => $query->whereIn('type', ['driver']) ] )
                                           ->documents->count() == 0 ) $missing_doc[] = __( 'driver' );
                            @endphp
                        <p class="text-sm font-medium leading-5 text-gray-500 ml-10 italic">
                            {!! __( 'helptext-personal-car', [
                                'profile' => route( 'edit-user', auth()->id() ),
                                'docs'    => implode( ', ', $missing_doc ) ] ) !!}
                        </p>
                        @endif
                    </x-input.group>
                    <x-input.group :error="$errors->first('extra_rental_car')" inline>
                        <x-input.checkbox wire:model="extra_rental_car" id="extra_rental_car" for="extra_rental_car">
                            {{ __('Rental car') }}
                        </x-input.checkbox>
                        @if ( $extra_rental_car &&
                              auth()->user()
                                    ->load( ['documents' => fn ($query) => $query->whereIn('type', ['driver']) ] )
                                    ->documents->count() == 0 )
                        <p class="text-sm font-medium leading-5 text-gray-500 ml-10 italic">
                            {!! __( 'helptext-rental-car', [ 'profile' => route( 'edit-user', auth()->id() ) ] ) !!}
                        </p>
                        @endif
                    </x-input.group>
                    <x-input.group :error="$errors->first('extra_parking')" inline>
                        <x-input.checkbox wire:model="extra_parking" id="extra_parking" for="extra_parking">
                            {{ __('Parking') }}
                        </x-input.checkbox>
                    </x-input.group>
                    @empty($mission->conf_amount)
                    <x-input.group :error="$errors->first('extra_registration')" inline>
                        <x-input.checkbox wire:model="extra_registration" id="extra_registration" for="extra_registration">
                            {{ __('Conference registration fee') }}
                        </x-input.checkbox>
                    </x-input.group>
                    @endempty
                </x-input.group>

                <x-input.group label="Others" for="extra_others" :error="$errors->first('extra_others')">
                    <x-input.textarea wire:model.lazy="extra_others" id="extra_others" rows="5" class="text-gray-700" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_extra">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@lang('Save')</x-button.primary>
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
