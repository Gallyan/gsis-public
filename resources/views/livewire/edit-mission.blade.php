<div class="relative">
    <form
        wire:submit.prevent="save"
        wire:reset.prevent="init"
        x-data="{ dirty : @entangle( 'modified' ) }"
        x-init="window.addEventListener('beforeunload', function(e) { if(dirty) { e.preventDefault(); e.returnValue = ''; } });"
    >
        @csrf

        <x-stickytopbar title="{{ __('Mission') }} {{ $mission->id }}" :modified="$modified" :disabled="$disabled" />

@push('scripts')
        <script type="text/javascript">
            Livewire.on('urlChange', param => {
                history.pushState(null, null, param);
            });
        </script>
@endpush

        <div class="text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:text-gray-400 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px">
                <li class="mr-2">
                    <a class="inline-block p-4 text-blue-600 rounded-t-lg border-b-2 border-blue-600 active cursor-pointer">{{ __('Mission') }}</a>
                </li>
                @if ( $mission->id && App\Models\Mission::find($mission->id)->status === 'processed' )
                <li class="mr-2">
                    <a href="{{ route( 'edit-expense', [$mission, $mission->expense] ) }}" class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300">{{ __('Related expenses') }}</a>
                </li>
                @else
                <li>
                    <a class="inline-block p-4 text-gray-400 rounded-t-lg cursor-not-allowed" title="{{ __('available-later') }}">{{ __('Related expenses') }}</a>
                </li>
                @endif
            </ul>
        </div>

        <div class="mt-6 sm:mt-5">
            @can('manage-users')
            <x-input.group label="User" class="sm:items-center text-cool-gray-600 sm:pb-5" paddingless borderless>
                <a href="{{ route('edit-user', $mission->user) }}" target="_blank" class="hover:underline pr-4">{{ $mission->user->name ?? '' }} <sup><x-icon.new-window /></sup></a>
                <a href="mailto:{{ $mission->user->email }}" class="pr-4"><x-icon.email /> {{ $mission->user->email }}</a>
                <span title="{{ __('Birthday') }}"><x-icon.birthday /> {{ $mission->user->birthday->format('d/m/Y') }}</span>
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
                <x-input.text wire:model.debounce.500ms="mission.subject" id="subject" :disabled="$disabled" />
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

            <x-input.group label="Mission order number" for="om" :error="$errors->first('mission.om')">
                @if ( in_array( $mission->status, ['in-progress','processed','cancelled'] ) )
                    <x-input.text wire:model.debounce.500ms="mission.om" id="om" :disabled="$disabled" />
                @else
                    <p class="mt-1 col-start-2 col-span-4 text-sm text-gray-500">
                        {{ __('om-later') }}
                    </p>
                @endif
            </x-input.group>

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
                    <p class="mt-1 col-start-2 col-span-4 text-sm text-gray-500">
                        {{ __('wp-sometimes') }}
                    </p>
                @endif
            </x-input.group>

            <x-input.group label="Conference" for="conference" :error="$errors->first('mission.conference')" required>
                <x-input.toggle
                    id="conference"
                    wire:model="mission.conference"
                    :before="'No'"
                    :after="'Yes'"
                    :choice="$mission->conference"
                    class="inline-flex mr-4"
                    :disabled="$disabled"
                />
                <span class="text-sm text-gray-500">{!! __('helptext-mission-conference') !!}</span>
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
            <x-input.group label="" for="" :error="$programme_errors->all()" borderless paddingless class="pb-5" innerclass="sm:col-start-2" >
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
                <ul role="list">
                    <li class="flex text-gray-500 border-dashed border-2 border-gray-300 rounded-md p-2 items-center @if ( in_array( $mission->programme->id, $del_docs ) ) line-through italic @endif">
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

                <x-input.group for="conf_amount" label="Registration fee to be paid by the institution" :error="$errors->first('mission.conf_amount')" class="mt-2" inline>
                    <x-input.money wire:model.debounce.500ms="mission.conf_amount" id="conf_amount" :disabled="$disabled" />
                </x-input.group>

                <x-input.group for="conf_currency" label="Currency" :error="$errors->first('mission.conf_currency')" class="mt-2" inline>
                    <x-input.currency wire:model="mission.conf_currency" id="conf_currency" :disabled="$disabled" />
                </x-input.group>

                @if ($mission->conf_amount)
                    <p class="ml-1 mt-2 text-sm font-bold text-gray-700">{!! __('helptext-conf-amount') !!}</p>
                @endif
            </x-input.group>
            @endif

            <x-input.group label="Destination" for="dest_country" :error="$errors->first('mission.dest_country')" required>
                <x-input.country wire:model="mission.dest_country" id="dest_country" placeholder="{{ __('Select Country...') }}" class="w-full" :disabled="$disabled" />
            </x-input.group>

            <x-input.group label="City" for="dest_city" :error="$errors->first('mission.dest_city')" required>
                <x-input.text wire:model.debounce.500ms="mission.dest_city" id="dest_city" leadingIcon="location" :disabled="$disabled" />
            </x-input.group>

            <x-input.group label="Departure" for="departure" :error="$errors->first('mission.departure')" required innerclass="flex">
                <div class="w-48">
                    <x-input.date wire:model="mission.departure" id="departure" placeholder="{{ __('YYYY-MM-DD') }}" :disabled="$disabled" />
                </div>
                <div class="flex flex-row items-center gap-2">
                    <p class="text-sm font-medium leading-5 text-gray-500 sm:mt-px">@lang('From'):</p>
                    <x-input.radiobar
                        id="from"
                        wire:model="mission.from"
                        :selected="$mission->from"
                        :keylabel="['work'=>'Work address','home'=>'Home address']"
                        :disabled="$disabled"
                    />
                </div>
            </x-input.group>

            <x-input.group label="Return" for="return" :error="$errors->first('mission.return')" required innerclass="flex">
                <div class="w-48">
                    <x-input.date wire:model="mission.return" id="return" placeholder="{{ __('YYYY-MM-DD') }}" :disabled="$disabled" />
                </div>
                <div class="flex flex-row items-center gap-2">
                    <p class="text-sm font-medium leading-5 text-gray-500 sm:mt-px">@lang('To'):</p>
                    <x-input.radiobar
                        id="to"
                        wire:model="mission.to"
                        :selected="$mission->to"
                        :keylabel="['work'=>'Work address','home'=>'Home address']"
                        :disabled="$disabled"
                    />
                </div>
            </x-input.group>

            <x-input.group label="Mission with or without costs" for="costs" :error="$errors->first('mission.costs')" required>
                <x-input.toggle
                    id="costs"
                    wire:model="mission.costs"
                    :before="'Without costs'"
                    :after="'With costs'"
                    :choice="$mission->costs"
                    class="inline-flex mr-4"
                    :disabled="$disabled"
                />
            </x-input.group>

            <x-input.group label="Transport Tickets" :error="$errors->first('mission.tickets')">

            @if ($mission->costs)
                <x-table>
                    <x-slot name="head">
                        <x-table.heading small>{{ __('Ticket') }}</x-table.heading>
                        <x-table.heading small>{{ __('Flight/Train No.') }}</x-table.heading>
                        <x-table.heading small>{{ __('Date') }}</x-table.heading>
                        <x-table.heading small>{{ __('transport-from') }}</x-table.heading>
                        <x-table.heading small>{{ __('transport-to') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @php $dirlist = []; @endphp
                        @forelse ($mission->tickets as $ticket)
                        @php $dirlist[] = $ticket['ticket_direction']; @endphp
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="ticket-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_ticket({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ __('ticket-dir-mode', [
                                            'mode' => __($ticket['ticket_mode']),
                                            'direction' => $ticket['ticket_direction'] ? __('Return') : __('Go')
                                            ]) }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_ticket({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $ticket['ticket_number'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_ticket({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ isset($ticket['ticket_date'])
                                            ? Illuminate\Support\Carbon::parse($ticket['ticket_date'])->format('d/m/Y')
                                            : '' }}
                                        {{ $ticket['ticket_time'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_ticket({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $ticket['ticket_from'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_ticket({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $ticket['ticket_to'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_ticket({{ $loop->iteration }})" class="text-cool-gray-600"  title="{{ __('Delete') }}">
                                        <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                    </x-button.link>
                                </span>
                            </x-table.cell>
                            @endif
                        </x-table.row>
                        @empty
                        <x-table.row>
                            <x-table.cell colspan="6">
                                <div class="flex justify-center items-center space-x-2">
                                    <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No tickets...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if( count( array_unique( $dirlist ) ) == 1 )
                <p class="text-sm font-medium leading-5 text-gray-500 mt-4 italic">
                    {{ __( 'helptext-go-return' )    }}
                </p>
                @endif

                @if (!$disabled)
                <x-button.secondary wire:click="$set('showTicket', true)" class="mt-4" :disabled="$disabled"><x-icon.plus/> {{ __('Add ticket') }}</x-button.secondary>
                @endif

            @else
                <p class="mt-1 col-start-2 col-span-4 text-sm text-gray-500">{{ __('This is a no-cost mission.') }}</p>
            @endif

            </x-input.group>

            <x-input.group label="Accomodations" :error="$errors->first('mission.hotels')">

            @if ($mission->costs)
                <x-table>
                    <x-slot name="head">
                        <x-table.heading small>{{ __('Hotel') }}</x-table.heading>
                        <x-table.heading small>{{ __('City') }}</x-table.heading>
                        <x-table.heading small>{{ __('Date') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse ($mission->hotels as $hotel)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="hotel-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_hotel({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $hotel['hotel_name'] }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_hotel({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ $hotel['hotel_city'] ?? '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_hotel({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ isset($hotel['hotel_start'])
                                            ? Illuminate\Support\Carbon::parse($hotel['hotel_start'])->format('d/m/Y')
                                            : '' }}
                                        <x-icon.arrow-right />
                                        {{ isset($hotel['hotel_end'])
                                            ? Illuminate\Support\Carbon::parse($hotel['hotel_end'])->format('d/m/Y')
                                            : '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_hotel({{ $loop->iteration }})" class="text-cool-gray-600"  title="{{ __('Delete') }}">
                                        <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                    </x-button.link>
                                </span>
                            </x-table.cell>
                            @endif
                        </x-table.row>
                        @empty
                        <x-table.row>
                            <x-table.cell colspan="4">
                                <div class="flex justify-center items-center space-x-2">
                                    <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No accomodation...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="$set('showHotel', true)" class="mt-4" :disabled="$disabled"><x-icon.plus/> {{ __('Add hotel') }}</x-button.secondary>
                @endif

            @else
                <p class="mt-1 col-start-2 col-span-4 text-sm text-gray-500">{{ __('This is a no-cost mission.') }}</p>
            @endif

            </x-input.group>

            <x-input.group label="Expected extra costs">

            @if ($mission->costs)
                <x-input.group paddingless borderless inline label="Meal" for="meal" :error="$errors->first('mission.meal')">
                    <x-input.radiobar
                        id="meal"
                        wire:model="mission.meal"
                        :selected="$mission->meal"
                        :keylabel="['forfait'=>'Flat-rate costs','reel'=>'Actual costs']"
                        :disabled="$disabled"
                    />
                    <p class="text-sm font-medium leading-5 text-gray-500 ml-2 mt-1 italic">
                    @if ( $mission->meal === 'reel' )
                        {!! __('repas-frais-reels') !!}
                    @elseif ( $mission->meal === 'forfait' )
                        {!! __('repas-forfaitaire') !!}
                    @endif
                    </p>
                </x-input.group>

                <x-input.group paddingless borderless inline label="Extra">
                    <x-input.group :error="$errors->first('mission.taxi')" inline>
                        <x-input.checkbox wire:model="mission.taxi" id="taxi" for="taxi" :disabled="$disabled">
                            {{ __('Taxi') }}
                        </x-input.checkbox>
                    </x-input.group>
                    <x-input.group :error="$errors->first('mission.transport')" inline>
                        <x-input.checkbox wire:model="mission.transport" id="transport" for="transport" :disabled="$disabled">
                            {{ __('Public transport') }}
                        </x-input.checkbox>
                    </x-input.group>
                    <x-input.group :error="$errors->first('mission.personal_car')" inline>
                        <x-input.checkbox wire:model="mission.personal_car" id="personal_car" for="personal_car" :disabled="$disabled">
                            {{ __('Private car') }}
                        </x-input.checkbox>

                        @if ( $mission->personal_car )
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
                    <x-input.group :error="$errors->first('mission.rental_car')" inline>
                        <x-input.checkbox wire:model="mission.rental_car" id="rental_car" for="rental_car" :disabled="$disabled">
                            {{ __('Rental car') }}
                        </x-input.checkbox>
                        @if ( $mission->rental_car &&
                              auth()->user()
                                    ->load( ['documents' => fn ($query) => $query->whereIn('type', ['driver']) ] )
                                    ->documents->count() == 0 )
                        <p class="text-sm font-medium leading-5 text-gray-500 ml-10 italic">
                            {!! __( 'helptext-rental-car', [ 'profile' => route( 'edit-user', auth()->id() ) ] ) !!}
                        </p>
                        @endif
                    </x-input.group>
                    <x-input.group :error="$errors->first('mission.parking')" inline>
                        <x-input.checkbox wire:model="mission.parking" id="parking" for="parking" :disabled="$disabled">
                            {{ __('Parking') }}
                        </x-input.checkbox>
                    </x-input.group>
                    @empty($mission->conf_amount)
                    <x-input.group :error="$errors->first('mission.registration')" inline>
                        <x-input.checkbox wire:model="mission.registration" id="registration" for="registration" :disabled="$disabled">
                            {{ __('Conference registration fee') }}
                        </x-input.checkbox>
                    </x-input.group>
                    @endempty
                </x-input.group>

                <x-input.group paddingless borderless inline label="Other expenses" for="others" :error="$errors->first('mission.others')">
                    <x-input.contenteditable wire:model="mission.others" id="others" leadingIcon="chat" :content="$mission->others" :disabled="$disabled"/>
                </x-input.group>

            @else
                <p class="mt-1 col-start-2 col-span-4 text-sm text-gray-500">{{ __('This is a no-cost mission.') }}</p>
            @endif

            </x-input.group>

            <x-input.group label="Comments" for="comments" :error="$errors->first('mission.comments')">
                <x-input.contenteditable wire:model="mission.comments" id="comments" leadingIcon="chat" :content="$mission->comments" :disabled="$disabled" />
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

                @if (!empty($mission->documents->filter(fn ($d) => $d->type != 'programme')))
                <ul role="list">
                @foreach( $mission->documents->filter(fn ($d) => $d->type != 'programme') as $document )
                    <li class="flex text-gray-500 border-dashed border-2 border-gray-300 rounded-md p-2 my-2 items-center @if ( in_array( $document->id, $del_docs ) ) line-through italic @endif">
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
            @can ('manage-users')
            <x-input.group label="Amount excl." for="amount" :error="$errors->first('mission.amount')" helpText="helptext-amount">
                <x-input.money wire:model.debounce.500ms="mission.amount" id="amount" :disabled="$disabled" />
            </x-input.group>
            @endcan
--}}
        </div>

    </form>

    <livewire:messagerie :object="$mission" />

    <!-- Edit ticket Modal -->
    <form wire:submit.prevent="save_ticket">
        @csrf

        <x-modal.dialog wire:model.defer="showTicket">
            <x-slot name="title">@lang('Edit ticket')</x-slot>

            <x-slot name="content">
                <x-input.group paddingless borderless class="sm:py-1" label="Direction" for="ticket_direction" :error="$errors->first('ticket_direction')" required >
                    <x-input.radiobar
                        id="ticket_direction"
                        wire:model="ticket_direction"
                        :selected="$ticket_direction"
                        :keylabel="['Go','Return']"
                    />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="Travel mode" for="ticket_mode" :error="$errors->first('ticket_mode')" required >
                    <x-input.select wire:model="ticket_mode" id="ticket_mode" class="w-full" placeholder="{{ __('Select travel mode...') }}" >
                        <x-slot name="leadingAddOn"><x-icon.rocket class="text-gray-400" /></x-slot>

                        <option value="Flight">{{ __('Flight') }}</option>
                        <option value="Train">{{ __('Train') }}</option>
                    </x-input.select>
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="Flight/Train No." for="ticket_number" :error="$errors->first('ticket_number')" >
                    <x-input.text wire:model.lazy="ticket_number" id="ticket_number" leadingIcon="ticket" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="Date" innerclass="sm:flex" required >
                    <x-input.group for="ticket_date" :error="$errors->first('ticket_date')" class="sm:w-1/2" inline>
                        <x-input.date wire:model.lazy="ticket_date" id="ticket_date" placeholder="{{ __('YYYY-MM-DD') }}" />
                    </x-input.group>
                    <x-input.group for="ticket_time" :error="$errors->first('ticket_time')" inline>
                        <x-input.time wire:model.lazy="ticket_time" id="ticket_time" />
                    </x-input.group>
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="City of departure" for="ticket_from" :error="$errors->first('ticket_from')" required>
                    <x-input.text wire:model.lazy="ticket_from " id="ticket_from" leadingIcon="location" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="City of arrival" for="ticket_to" :error="$errors->first('ticket_to')" required >
                    <x-input.text wire:model.lazy="ticket_to " id="ticket_to" leadingIcon="location" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_ticket">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@lang('Save')</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

    <!-- Edit hotel Modal -->
    <form wire:submit.prevent="save_hotel">
        @csrf

        <x-modal.dialog wire:model.defer="showHotel">
            <x-slot name="title">@lang('Edit hotel')</x-slot>

            <x-slot name="content">
                <x-input.group paddingless borderless class="sm:py-1" label="Name" for="hotel_name" :error="$errors->first('hotel_name')" >
                    <x-input.text wire:model.lazy="hotel_name" id="hotel_name" leadingIcon="building" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="City" for="hotel_city" :error="$errors->first('hotel_city')" >
                    <x-input.text wire:model.lazy="hotel_city" id="hotel_city" leadingIcon="location" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="Start" for="hotel_start" :error="$errors->first('hotel_start')" >
                    <x-input.date wire:model.lazy="hotel_start" id="hotel_start" placeholder="{{ __('YYYY-MM-DD') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="End" for="hotel_end" :error="$errors->first('hotel_end')" >
                    <x-input.date wire:model.lazy="hotel_end" id="hotel_end" placeholder="{{ __('YYYY-MM-DD') }}" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_hotel">{{ __('Cancel') }}</x-button.secondary>

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
            <x-button.primary wire:click="$toggle('showInformationMessage')">{{ __('Ok') }}</x-button.primary>
        </x-slot>
    </x-modal.information>

</div>
