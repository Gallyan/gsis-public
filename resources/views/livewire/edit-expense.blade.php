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
                    <a href="{{ route( 'edit-mission', [$mission] ) }}" class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300">{{ __('Mission') }}</a>
                </li>
                <li class="mr-2">
                    <a class="inline-block p-4 text-blue-600 rounded-t-lg border-b-2 border-blue-600 active cursor-pointer">{{ __('Related expenses') }}</a>
                </li>
            </ul>
        </div>

        <div class="mt-6 sm:mt-5">
            @can('manage-users')
            <x-input.group label="User" class="sm:items-center text-cool-gray-600 scr:sm:pb-5 print:text-black" paddingless borderless>
                <a href="{{ route('edit-user', $mission->user) }}" target="_blank" class="hover:underline pr-4">{{ $mission->user->name ?? '' }} <sup><x-icon.new-window /></sup></a>
                <a href="mailto:{{ $mission->user->email }}" class="pr-4"><x-icon.email /> {{ $mission->user->email }}</a>
                <span title="{{ __('Birthday') }}"><x-icon.birthday /> {{ $mission->user->birthday->format('d/m/Y') }}</span>
            </x-input.group>
            @endcan

            <x-input.group label="Manager" class="sm:items-center text-cool-gray-600 scr:sm:pb-5 print:text-black" innerclass="flex items-center" :borderless="!$isAuthManager" :paddingless="!$isAuthManager">
               {{ $mission->managers->isNotEmpty() ?
                    $mission->managers->map(fn($mgr) => App\Models\User::find($mgr->user_id)->name)->implode(', ') :
                    __('There is no manager yet.') }}
            </x-input.group>

            <x-input.group label="Mission" class="sm:items-center text-cool-gray-600 scr:sm:pb-5 print:text-black">
                {{ $mission->subject ?? '' }}
                @if ( !is_null($mission->subject) && !is_null($mission->om) ) / @endif
                {{ $mission->om ?? '' }}
            </x-input.group>

            <x-input.group label="Status" for="status" :error="$errors->first('expense.status')" helpText="{!! __('helptext-status') !!}" required>
                <x-input.status
                    id="status"
                    wire:model="expense.status"
                    :disabled="$disabledStatuses"
                    :selected="$expense->status"
                    :keylabel="$expense->allStatuses"
                />
            </x-input.group>

            @if ( isset($mission->meal) &&
                  $mission->meal === 'reel' )

            <x-input.group label="Meals at actual cost">

                <x-table>
                    <x-slot name="head">
                        <x-table.heading small>{{ __('Date') }}</x-table.heading>
                        <x-table.heading small>{{ __('Lunch') . ' / ' . __('Dinner') }}</x-table.heading>
                        <x-table.heading small>{{ __('Amount') }}</x-table.heading>
                        <x-table.heading small>{{ __('Currency') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse($expense->actual_costs_meals as $acm)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="acm-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.actual_costs_meals.'.$loop->index.'.acm_date')"
                                    inline>
                                    <x-input.date wire:model.lazy="expense.actual_costs_meals.{{$loop->index}}.acm_date" id="expense.actual_costs_meals.{{$loop->index}}.acm_date" placeholder="{{ __('YYYY-MM-DD') }}" :disabled="$disabled"/>
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.actual_costs_meals.'.$loop->index.'.acm_type')"
                                    inline>
                                    <x-input.radiobar
                                        id="expense.actual_costs_meals.{{$loop->index}}.acm_type"
                                        wire:model="expense.actual_costs_meals.{{$loop->index}}.acm_type"
                                        :selected="$expense->actual_costs_meals[$loop->index]['acm_type']"
                                        :keylabel="['lunch'=>'Lunch','dinner'=>'Dinner']"
                                        :disabled="$disabled"
                                    />
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.actual_costs_meals.'.$loop->index.'.acm_amount')" inline>
                                    <x-input.money wire:model.debounce.500ms="expense.actual_costs_meals.{{$loop->index}}.acm_amount" id="expense.actual_costs_meals.{{$loop->index}}.acm_amount" :disabled="$disabled" class="min-w-32" />
                                </x-input>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.actual_costs_meals.'.$loop->index.'.acm_currency')" inline>
                                    <x-input.currency wire:model="expense.actual_costs_meals.{{$loop->index}}.acm_currency" id="expense.actual_costs_meals.{{$loop->index}}.acm_currency" class="max-w-36" :disabled="$disabled" />
                                </x-input.group>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center print:hidden">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_acm({{ $loop->iteration }})" class="text-cool-gray-600" title="{{ __('Delete') }}" :disabled="$disabled">
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
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No meals...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="add_acm" class="mt-4" :disabled="$disabled">
                    <x-icon.plus/> {{ __('Add meal') }}
                </x-button.secondary>
                @endif

            </x-input.group>

            @elseif ( isset($mission->meal) &&
                      $mission->meal === 'forfait' )

            <x-input.group label="Meals at flat-rate cost" innerclass="flex flex-row flex-wrap gap-x-10 gap-y-2">
                    <x-input.group label="No. of lunches" inline
                        class="inline-flex space-x-2 text-sm leading-5"
                        :error="$errors->first('expense.flat_rate_lunch')" >
                        <x-input.number wire:model="expense.flat_rate_lunch" :disabled="$disabled" placeholder="0" />
                    </x-input.group>
                    <x-input.group label="No. of dinners" inline
                        class="inline-flex space-x-2 text-sm leading-5"
                        :error="$errors->first('expense.flat_rate_dinner')" >
                        <x-input.number wire:model="expense.flat_rate_dinner" :disabled="$disabled" placeholder="0" />
                    </x-input.group>
            </x-input.group>

            @endif

            <x-input.group label="Transports">

                <x-table>
                    <x-slot name="head">
                        <x-table.heading small class="w-32">{{ __('Date') }}</x-table.heading>
                        <x-table.heading small>{{ __('Info') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6 print:hidden"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse($expense->transports as $transport)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="transport-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">

                            <x-table.cell class="whitespace-normal text-center cursor-pointer w-32" wire:click="edit_transport({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        {{ isset($transport['transport_date'])
                                            ? Illuminate\Support\Carbon::parse($transport['transport_date'])->format('d/m/Y')
                                            : '' }}
                                    </p>
                                </span>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center cursor-pointer" wire:click="edit_transport({{ $loop->iteration }})">
                                <span class="inline-flex space-x-2 text-sm leading-5">
                                    <p class="text-cool-gray-600">
                                        @switch($transport['transport_mode'])
                                            @case('train')
                                                {{ __('Train to :dest', ['dest'=>$transport['transport_dest']]) }}&nbsp;<x-icon.arrow-right />&nbsp;{{ number_format( (float)$transport['transport_amount'],2,',',' ') }}&nbsp;{{ __($transport['transport_currency'] ? Lang::has('currencies.symbol-'.$transport['transport_currency']) ? 'currencies.symbol-'.$transport['transport_currency'] : $transport['transport_currency'] : '') }}
                                                @break

                                            @case('flight')
                                                {{ __('Flight to :dest', ['dest'=>$transport['transport_dest']]) }}&nbsp;<x-icon.arrow-right />&nbsp;{{ number_format( (float)$transport['transport_amount'],2,',',' ') }}&nbsp;{{ __($transport['transport_currency'] ? Lang::has('currencies.symbol-'.$transport['transport_currency']) ? 'currencies.symbol-'.$transport['transport_currency'] : $transport['transport_currency'] : '') }}
                                                @break

                                            @case('public')
                                                {{ __('transport-public',[
                                                    'type' => e($transport['transport_type']),
                                                    'nb' => e($transport['transport_number'])
                                                    ])}}&nbsp;<x-icon.arrow-right />&nbsp;{{ number_format( (float)$transport['transport_amount'],2,',',' ') }}&nbsp;{{ __($transport['transport_currency'] ? Lang::has('currencies.symbol-'.$transport['transport_currency']) ? 'currencies.symbol-'.$transport['transport_currency'] : $transport['transport_currency'] : '') }}
                                                @break

                                            @case('taxi')
                                                {{ __('Taxi') }}&nbsp;{{ $transport['transport_route'] }}&nbsp;<x-icon.arrow-right />&nbsp;{{ number_format( (float)$transport['transport_amount'],2,',',' ') }}&nbsp;{{ __($transport['transport_currency'] ? Lang::has('currencies.symbol-'.$transport['transport_currency']) ? 'currencies.symbol-'.$transport['transport_currency'] : $transport['transport_currency'] : '') }}
                                                @break

                                            @case('personal')
                                                {{ __('Personal car') }}, {{ $transport['transport_dist'] }} km, {{ $transport['transport_route'] }}
                                                @break
                                        @endswitch
                                    </p>
                                </span>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center pl-2 pr-2 print:hidden">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_transport({{ $loop->iteration }})" class="text-cool-gray-600" title="{{ __('Delete') }}" :disabled="$disabled">
                                        <x-icon.trash class="h-4 w-4 text-cool-gray-400" />
                                    </x-button.link>
                                </span>
                            </x-table.cell>
                            @endif
                        </x-table.row>
                        @empty
                        <x-table.row>
                            <x-table.cell colspan="3">
                                <div class="flex justify-center items-center space-x-2">
                                    <x-icon.inbox class="h-6 w-6 text-cool-gray-400" />
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No transport...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="$set('showTransport', true)" class="mt-4" :disabled="$disabled">
                    <x-icon.plus/> {{ __('Add transport') }}
                </x-button.secondary>
                @endif

            </x-input.group>

            <x-input.group label="Hotel(s)">

                <x-table>
                    <x-slot name="head">
                        <x-table.heading small>{{ __('Date') }}</x-table.heading>
                        <x-table.heading small>{{ __('Name of the hotel') }}</x-table.heading>
                        <x-table.heading small class="w-28">{{ __('No. of nights') }}</x-table.heading>
                        <x-table.heading small class="w-48">{{ __('Amount') }}</x-table.heading>
                        <x-table.heading small>{{ __('Currency') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6 print:hidden"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse($expense->hotels as $hotel)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="hotel-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.hotels.'.$loop->index.'.hotel_date')"
                                    inline>
                                    <x-input.date wire:model.lazy="expense.hotels.{{$loop->index}}.hotel_date" id="expense.hotels.{{$loop->index}}.hotel_date" placeholder="{{ __('YYYY-MM-DD') }}" :disabled="$disabled" :print="$hotel['hotel_date']"/>
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.hotels.'.$loop->index.'.hotel_name')"
                                    inline>
                                    <x-input.text
                                        id="expense.hotels.{{$loop->index}}.hotel_name"
                                        wire:model.debounce.500ms="expense.hotels.{{$loop->index}}.hotel_name"
                                        placeholder="" leadingIcon=""
                                        :disabled="$disabled"
                                        :print="$hotel['hotel_name']"
                                    />
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.hotels.'.$loop->index.'.hotel_nights')"
                                    inline>
                                    <x-input.number
                                        id="expense.hotels.{{$loop->index}}.hotel_nights"
                                        wire:model.debounce.500ms="expense.hotels.{{$loop->index}}.hotel_nights"
                                        placeholder="0" leadingIcon=""
                                        :disabled="$disabled"
                                        :print="$hotel['hotel_nights']"
                                    />
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.hotels.'.$loop->index.'.hotel_amount')" inline>
                                    <x-input.money wire:model.debounce.500ms="expense.hotels.{{$loop->index}}.hotel_amount" id="expense.hotels.{{$loop->index}}.hotel_amount" :disabled="$disabled" class="min-w-32" :print="$hotel['hotel_amount']" />
                                </x-input>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.hotels.'.$loop->index.'.hotel_currency')" inline>
                                    <x-input.currency wire:model="expense.hotels.{{$loop->index}}.hotel_currency" id="expense.hotels.{{$loop->index}}.hotel_currency" class="max-w-36" :disabled="$disabled" :print="$hotel['hotel_currency']" />
                                </x-input.group>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center print:hidden">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_hotel({{ $loop->iteration }})" class="text-cool-gray-600" title="{{ __('Delete') }}" :disabled="$disabled">
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
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No hotels...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="add_hotel" class="mt-4" :disabled="$disabled">
                    <x-icon.plus/> {{ __('Add hotel') }}
                </x-button.secondary>
                @endif

            </x-input.group>

            <x-input.group label="Conference registration fees" helpText="{{ __('helptext-conf-fees') }}">

                <x-table>
                    <x-slot name="head">
                        <x-table.heading small>{{ __('Conference') }}</x-table.heading>
                        <x-table.heading small class="w-48">{{ __('Amount') }}</x-table.heading>
                        <x-table.heading small>{{ __('Currency') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6 print:hidden"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse($expense->registrations as $registration)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="reg-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">
                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.registrations.'.$loop->index.'.reg_name')"
                                    inline>
                                    <x-input.text
                                        id="expense.registrations.{{$loop->index}}.reg_name"
                                        wire:model.debounce.500ms="expense.registrations.{{$loop->index}}.reg_name"
                                        placeholder="" leadingIcon=""
                                        :disabled="$disabled"
                                        :print="$registration['reg_name']"
                                    />
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.registrations.'.$loop->index.'.reg_amount')" inline>
                                    <x-input.money wire:model.debounce.500ms="expense.registrations.{{$loop->index}}.reg_amount" id="expense.registrations.{{$loop->index}}.reg_amount" :disabled="$disabled" class="min-w-32" :print="$registration['reg_amount']" />
                                </x-input>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.registrations.'.$loop->index.'.reg_currency')" inline>
                                    <x-input.currency wire:model="expense.registrations.{{$loop->index}}.reg_currency" id="expense.registrations.{{$loop->index}}.reg_currency" class="max-w-36" :disabled="$disabled" :print="$registration['reg_currency']" />
                                </x-input.group>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center print:hidden">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_reg({{ $loop->iteration }})" class="text-cool-gray-600" title="{{ __('Delete') }}" :disabled="$disabled">
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
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No registrations...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="add_reg" class="mt-4" :disabled="$disabled">
                    <x-icon.plus/> {{ __('Add registration') }}
                </x-button.secondary>
                @endif

            </x-input.group>

            <x-input.group label="Misc">

                <x-table>
                    <x-slot name="head">
                        <x-table.heading small>{{ __('Object') }}</x-table.heading>
                        <x-table.heading small>{{ __('Date') }}</x-table.heading>
                        <x-table.heading small class="w-48">{{ __('Amount') }}</x-table.heading>
                        <x-table.heading small>{{ __('Currency') }}</x-table.heading>
                        @if(!$disabled)
                        <x-table.heading small class="w-6 print:hidden"></x-table.heading>
                        @endif
                    </x-slot>

                    <x-slot name="body">
                        @forelse($expense->miscs as $misc)
                        <x-table.row wire:loading.class.delay="opacity-50" wire:key="misc-{{ $loop->iteration }}" class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-50' : '' }}">

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.miscs.'.$loop->index.'.misc_object')"
                                    inline>
                                    <x-input.text
                                        id="expense.miscs.{{$loop->index}}.misc_object"
                                        wire:model.debounce.500ms="expense.miscs.{{$loop->index}}.misc_object"
                                        placeholder="" leadingIcon=""
                                        :disabled="$disabled"
                                        :print="$misc['misc_object']"
                                    />
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group
                                    class="inline-flex space-x-2 text-sm leading-5"
                                    :error="$errors->first('expense.miscs.'.$loop->index.'.misc_date')"
                                    inline>
                                    <x-input.date wire:model.lazy="expense.miscs.{{$loop->index}}.misc_date" id="expense.miscs.{{$loop->index}}.misc_date" placeholder="{{ __('YYYY-MM-DD') }}" :disabled="$disabled" :print="$misc['misc_date']"/>
                                </x-input.group>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.miscs.'.$loop->index.'.misc_amount')" inline>
                                    <x-input.money wire:model.debounce.500ms="expense.miscs.{{$loop->index}}.misc_amount" id="expense.miscs.{{$loop->index}}.misc_amount" :disabled="$disabled" class="min-w-32" :print="$misc['misc_amount']"/>
                                </x-input>
                            </x-table.cell>

                            <x-table.cell class="whitespace-normal text-center">
                                <x-input.group class="inline-flex space-x-2 text-sm leading-5" :error="$errors->first('expense.miscs.'.$loop->index.'.misc_currency')" inline>
                                    <x-input.currency wire:model="expense.miscs.{{$loop->index}}.misc_currency" id="expense.miscs.{{$loop->index}}.misc_currency" class="max-w-36" :disabled="$disabled" :print="$misc['misc_currency']"/>
                                </x-input.group>
                            </x-table.cell>

                            @if(!$disabled)
                            <x-table.cell class="whitespace-nowrap text-center print:hidden">
                                <span class="inline-flex text-sm leading-5">
                                    <x-button.link wire:click="del_misc({{ $loop->iteration }})" class="text-cool-gray-600" title="{{ __('Delete') }}" :disabled="$disabled">
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
                                    <span class="font-medium text-cool-gray-400 text-lg">{{ __('No misc expenses...') }}</span>
                                </div>
                            </x-table.cell>
                        </x-table.row>
                        @endforelse
                    </x-slot>
                </x-table>

                @if (!$disabled)
                <x-button.secondary wire:click="add_misc" class="mt-4" :disabled="$disabled">
                    <x-icon.plus/> {{ __('Add misc expense') }}
                </x-button.secondary>
                @endif

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

                @if (!empty($expense->documents))
                <ul role="list">
                @foreach( $expense->documents as $document )
                    <li class="flex text-gray-500 border-dashed border-2 border-gray-300 rounded-md p-2 my-2 items-center @if ( in_array( $document->id, $del_docs ) ) line-through italic @endif">
                        <x-icon.document class="w-10 h-10 text-gray-500" />
                        <div class="mx-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <a href="{{ route( 'download', $document->id ) }}" class="hover:underline">{{ $document->name }}</a> <span class="text-sm text-gray-500">({{ $document->sizeForHumans }}) {{ __('Added :date',[ 'date' => $document->created_at->diffForHumans() ]) }}</span>
                            </p>
                            <p class="text-sm text-gray-500">{{ __($document->type) }}</p>
                        </div>
                        @if ( !in_array( $document->id, $del_docs ) && !$disabled )
                        <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer print:hidden" wire:click="del_doc({{ $document->id }})"/>
                        @endif
                    </li>
                @endforeach
                </ul>
                @endif

                @if (count($expense->documents)>1)
                <p class="text-sm font-medium text-gray-700">
                    <a href="{{ route( 'zip-expense', [$mission, $mission->expense] ) }}" class="hover:underline">
                        {{ __('Download all files in one zip') }}
                        <x-icon.download class="text-gray-500"/>
                    </a>
                </p>
                @endif


            </x-input.group>

            <x-input.group label="Comments" for="comments" :error="$errors->first('expense.comments')">
                <x-input.contenteditable wire:model="expense.comments" id="comments" :content="$expense->comments" class="text-gray-700" :disabled="$disabled" leadingIcon="chat" />
            </x-input.group>

        </div>

    </form>

    <livewire:messagerie key="msg" :object="$expense" />

    <!-- Edit transport Modal -->
    <form wire:submit.prevent="save_transport">
        @csrf

        <x-modal.dialog wire:model.defer="showTransport">
            <x-slot name="title">@lang('Edit transport')</x-slot>

            <x-slot name="content">
                <x-input.group paddingless borderless class="sm:py-1 sm:items-center" label="Travel mode" for="transport_mode" :error="$errors->first('transport_mode')" required >
                    <x-input.select wire:model="transport_mode" id="transport_mode" class="w-full" placeholder="{{ __('Select travel mode...') }}" >
                        <x-slot name="leadingAddOn"><x-icon.rocket class="text-gray-400" /></x-slot>
                        @foreach (\App\Models\Expense::TRANSPORTS as $k=>$v)
                            <option value="{{ $k }}">{{ __($v) }}</option>
                        @endforeach
                    </x-input.select>
                </x-input.group>

            @if( $transport_mode )
                <x-input.group paddingless borderless class="sm:py-1" label="Date" for="transport_date" :error="$errors->first('transport_date')" required >
                    <x-input.date wire:model="transport_date" id="transport_date" placeholder="{{ __('YYYY-MM-DD') }}" />
                </x-input.group>

                @if( $transport_mode === 'personal' )
                <x-input.group paddingless borderless class="sm:py-1" for="transport_dist" label="No. of km" :error="$errors->first('transport_dist')" required >
                    <x-input.number wire:model.debounce="transport_dist" id="transport_dist" placeholder="0" leadingIcon="ruler" />
                </x-input.group>
                @endif

                @if( in_array( $transport_mode, ['train','flight'] ) )
                <x-input.group paddingless borderless class="sm:py-1" for="transport_dest" label="Destination" :error="$errors->first('transport_dest')" required >
                    <x-input.text wire:model.debounce="transport_dest" id="transport_dest" placeholder="{{ __('Destination') }}" leadingIcon="location" />
                </x-input.group>

                @elseif( $transport_mode === 'public' )
                <x-input.group paddingless borderless class="sm:py-1 sm:items-center" for="transport_type" label="Transport type" :error="$errors->first('transport_type')" required >
                    <x-input.text wire:model.debounce="transport_type" id="transport_type" placeholder="{{ __('Transport type') }}" />
                </x-input.group>
                <x-input.group paddingless borderless class="sm:py-1" for="transport_number" label="No. of tickets" :error="$errors->first('transport_number')" required >
                    <x-input.number wire:model.debounce="transport_number" id="transport_number" placeholder="0" leadingIcon="calc" />
                </x-input.group>

                @elseif( in_array( $transport_mode, ['taxi','personal'] ) )
                <x-input.group paddingless borderless class="sm:py-1 sm:items-center" for="transport_route" label="Route" :error="$errors->first('transport_route')" required >
                    <x-input.text wire:model.debounce="transport_route" id="transport_route" placeholder="{{ __('Departure / Arrival') }}" leadingIcon="location" />
                </x-input.group>

                @endif

                @if( in_array( $transport_mode, ['train','flight','public','taxi'] ) )

                <x-input.group paddingless borderless class="sm:py-1" for="transport_amount" label="Amount" :error="$errors->first('transport_amount')" required >
                    <x-input.money wire:model.debounce.500ms="transport_amount" id="transport_amount" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="transport_currency" label="Currency" :error="$errors->first('transport_currency')" required >
                    <x-input.currency wire:model="transport_currency" id="transport_currency" />
                </x-input.group>

                @endif

            @endif

            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_transport">{{ __('Cancel') }}</x-button.secondary>

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

@pushOnce('stylesheets')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endPushOnce

@pushOnce('scripts')
    <script src="https://unpkg.com/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
@endPushOnce