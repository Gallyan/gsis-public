<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Purchase orders') }}</h1>

    <div class="py-4 space-y-4">
        <!-- Top Bar -->
        <div class="flex justify-between flex-wrap gap-2">
            <div class="flex space-x-2">
                <x-input.text wire:model="filters.search" placeholder="{{ __('Search...') }}">
                    <x-slot name="leadingAddOn"><x-icon.magnifier class="text-gray-400"/></x-slot>
                </x-input.text>

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
            <div class="bg-cool-gray-200 p-4 rounded shadow-inner flex relative">
                <div class="w-1/2 pr-2 space-y-4">
                    <x-input.group inline for="filter-institution" label="Institution">
                        <x-input.select wire:model="filters.institution" id="filter-institution" class="w-full" placeholder="{{ __('Select Institution...') }}">
                            @foreach (\App\Models\Institution::all()->sortBy('name') as $ins)
                            <option value="{{ $ins->id }}">{{ $ins->name }} / {{ $ins->contract }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>

                    <x-input.group inline for="filter-status" label="Status">
                        <x-input.select wire:model="filters.status" id="filter-status" class="w-full" placeholder="{{ __('Select Status...') }}">
                            @foreach (\App\Models\Order::STATUSES as $key => $label)
                            <option value="{{ $key }}">{{ __($label) }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>

                    @can('manage-users')
                    <x-input.group inline for="filter-user" label="User">
                        <x-input.text wire:model.debounce.500ms="filters.user" id="filter-user" />
                    </x-input.group>
                    @endcan
                </div>

                <div class="w-1/2 pl-2 space-y-4">
                    <x-input.group inline for="filter-date-min" label="Created after">
                        <x-input.date wire:model="filters.date-min" id="filter-date-min" placeholder="{{ __('YYYY-MM-DD') }}" />
                    </x-input.group>

                    <x-input.group inline for="filter-date-max" label="Created before">
                        <x-input.date wire:model="filters.date-max" id="filter-date-max" placeholder="{{ __('YYYY-MM-DD') }}" />
                    </x-input.group>

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
                    <x-table.heading sortable multi-column wire:click="sortBy('subject')" :direction="$sorts['subject'] ?? null" class="w-full">{{ __('Subject') }}</x-table.heading>
                    @can ('manage-users')
                    <x-table.heading sortable multi-column wire:click="sortBy('user_id')" :direction="$sorts['user_id'] ?? null">{{ __('User') }}</x-table.heading>
                    @endcan
                    <x-table.heading sortable multi-column wire:click="sortBy('institution_id')" :direction="$sorts['institution_id'] ?? null">{{ __('Institution') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('status')" :direction="$sorts['status'] ?? null">{{ __('Status') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['created_at'] ?? null">{{ __('Created') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($orders as $order)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $order->id }}" wire:click="edit({{ $order->id }})" class="cursor-pointer">
                        <x-table.cell>
                            <span class="inline-flex space-x-2 text-sm leading-5">
                                <p class="text-cool-gray-600 truncate max-w-md">
                                    {{ $order->subject }}
                                </p>
                            </span>
                        </x-table.cell>

                        @can ('manage-users')
                        <x-table.cell>
                            <span class="inline-flex space-x-2 text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $order->user->full_name }}
                                </p>
                            </span>
                        </x-table.cell>
                        @endcan

                        <x-table.cell>
                            <span class="inline-flex space-x-2 text-sm leading-5 max-w-100">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $order->institution->name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ __(App\Models\Order::STATUSES[$order->status]) }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $order->date_for_humans }}
                                </p>
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
                {{ $orders->links() }}
            </div>

        </div>
    </div>

</div>