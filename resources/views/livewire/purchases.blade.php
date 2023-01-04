<div>
    <h1 class="text-2xl font-semibold text-gray-900">{!! __('Non-mission purchases') !!}</h1>

    <div class="py-4 space-y-4">
        <!-- Top Bar -->
        <div class="flex justify-between flex-wrap gap-2">
            <div class="flex space-x-2">
                <x-input.text wire:model="filters.search" placeholder="{{ __('Search...') }}" leadingIcon="magnifier" />

                <x-button.link wire:click="toggleShowFilters">@if ($showFilters) {{ __('Hide') }} @endif {{ __('Advanced Search') }}...</x-button.link>
            </div>

            <div class="space-x-2 flex items-center">
                <x-input.group inline for="perPage" label="Per Page" class="flex flex-row gap-2 items-center">
                    <x-input.select wire:model="perPage" id="perPage" class="w-20">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </x-input.select>
                </x-input.group>

                <x-button.primary wire:click="create"><x-icon.plus/> {{ __('New') }}</x-button.primary>
            </div>
        </div>

        <!-- Advanced Search -->
        <div>
            @if ($showFilters)
            <div class="bg-cool-gray-200 p-4 rounded shadow-inner flex relative flex-row flex-wrap">
                <div class="sm:w-1/2 w-full pr-2 space-y-4">
                    <x-input.group inline for="filter-institution" label="Institution">
                        <x-input.select wire:model="filters.institution" id="filter-institution" class="w-full" multiple>
                            @foreach (\App\Models\Institution::all()->sortBy('name') as $ins)
                            <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>

                    <x-input.group inline for="filter-status" label="Status">
                        <x-input.select wire:model="filters.status" id="filter-status" class="w-full" multiple>
                            @foreach (\App\Models\Purchase::STATUSES as $key => $label)
                            <option value="{{ $key }}">{{ __($label) }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>
                </div>

                <div class="sm:w-1/2 w-full pl-2 space-y-4 pt-4 sm:pt-2">
                    @can('manage-users')
                    <x-input.group inline for="filter-user" label="User">
                        <x-input.text wire:model.debounce.500ms="filters.user" id="filter-user" leadingIcon="user" />
                    </x-input.group>
                    @endcan

                    <div class="flex flex-row flex-wrap">
                        <x-input.group inline for="filter-date-min" label="Created after" class="xl:w-1/2 w-full">
                            <x-input.date wire:model="filters.date-min" id="filter-date-min" placeholder="{{ __('YYYY-MM-DD') }}" />
                        </x-input.group>

                        <x-input.group inline for="filter-date-max" label="Created before" class="xl:w-1/2 w-full pt-4 xl:pt-0">
                            <x-input.date wire:model="filters.date-max" id="filter-date-max" placeholder="{{ __('YYYY-MM-DD') }}" />
                        </x-input.group>
                    </div>

                    @can('manage-users')
                    <x-input.group inline for="filter-manager" label="Manager">
                        <x-input.select wire:model="filters.manager" id="filter-manager" class="w-full" placeholder="{{ __('Select a manager...') }}">
                            <x-slot name="leadingAddOn"><x-icon.user /></x-slot>

                            @foreach ($allmanagers as $id => $fullname)
                            <option value="{{ $id }}">{{ $fullname }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>
                    @endcan

                    <div class="pt-5">
                        <x-button.link wire:click="resetFilters" class="absolute right-0 bottom-0 p-4">{{ __('Reset Filters') }}</x-button.link>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Table -->
        <div class="flex-col space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading sortable multi-column wire:click="sortBy('subject')" :direction="$sorts['subject'] ?? null" class="max-w-96">{{ __('Subject') }}</x-table.heading>
                    @can ('manage-users')
                    <x-table.heading sortable multi-column wire:click="sortBy('users.lastname')" :direction="$sorts['users.lastname'] ?? null">{{ __('User') }}</x-table.heading>
                    @endcan
                    <x-table.heading sortable multi-column wire:click="sortBy('ins_name')" :direction="$sorts['ins_name'] ?? null">{{ __('Institution') }}</x-table.heading>
                    <x-table.heading>{{ __('Manager') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('status')" :direction="$sorts['status'] ?? null">{{ __('Status') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('purchases.created_at')" :direction="$sorts['purchases.created_at'] ?? null">{{ __('Created') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($purchases as $purchase)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $purchase->id }}" wire:click="edit({{ $purchase->id }})" class="cursor-pointer hover:bg-cool-gray-50">
                        <x-table.cell class="whitespace-normal">
                            <span class="inline-flex space-x-2 text-sm leading-5">
                                <p class="text-cool-gray-600" title="{{ $purchase->subject }}">
                                    {{ $purchase->subject }}
                                </p>
                            </span>
                        </x-table.cell>

                        @can ('manage-users')
                        <x-table.cell class="whitespace-nowrap">
                            <span class="inline-flex space-x-2 text-sm leading-5">
                                <p class="text-cool-gray-600 truncate" title="{{ $purchase->firstname }} {{ $purchase->lastname }}">
                                    {{ $purchase->firstname }} {{ $purchase->lastname }}
                                </p>
                            </span>
                        </x-table.cell>
                        @endcan

                        <x-table.cell class="whitespace-nowrap">
                            <span class="inline-flex space-x-2 text-sm leading-5 max-w-[180px]">
                                <p class="text-cool-gray-600 truncate" title="{{ $purchase->ins_name }} / {{ $purchase->ins_contract }}">
                                    {{ $purchase->ins_name }} / {{ $purchase->ins_contract }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell class="whitespace-nowrap">
                            <span class="inline-flex space-x-2 text-sm leading-5 max-w-100">
                                <p class="text-cool-gray-600 break-all">
                                    @foreach ($purchase->managers->pluck('user_id')->unique() as $id)
                                        {{ App\Models\User::find($id)->name ?? '' }}<br />
                                    @endforeach
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell class="whitespace-nowrap">
                            <span class="inline-flex space-x-2 text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ __(App\Models\Purchase::STATUSES[$purchase->status]) }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell class="whitespace-nowrap">
                            <span
                                class="inline-flex space-x-2 truncate text-sm leading-5 text-cool-gray-600"
                                title="{{ $purchase->created_at }}"
                            >
                                {{ $purchase->date_for_humans }}
                            </span>
                        </x-table.cell>
                    </x-table.row>
                    @empty
                    <x-table.row>
                        <x-table.cell colspan="6">
                            <div class="flex justify-center items-center space-x-2">
                                <x-icon.inbox class="h-8 w-8 text-cool-gray-400" />
                                <span class="font-medium py-8 text-cool-gray-400 text-xl">{{ __('Nothing found...') }}</span>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                    @endforelse
                </x-slot>
            </x-table>

            <div>
                {{ $purchases->links() }}
            </div>

        </div>
    </div>

</div>


@pushOnce('stylesheets')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endPushOnce

@pushOnce('scripts')
    <script src="https://unpkg.com/moment"></script>
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
@endPushOnce