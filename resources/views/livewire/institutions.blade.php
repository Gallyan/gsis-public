<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Institutions') }}</h1>

    <div class="py-4 space-y-4">
        <!-- Top Bar -->
        <div class="flex justify-between flex-wrap gap-2">
            <div class="flex space-x-2">
                <x-input.text wire:model="search" placeholder="{{ __('Search...') }}">
                    <x-slot name="leadingAddOn"><x-icon.magnifier class="text-gray-400"/></x-slot>
                </x-input.text>
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

        <!-- Table -->
        <div class="flex-col space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null" class="w-full">{{ __('Name') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('contract')" :direction="$sorts['contract'] ?? null">{{ __('Contract') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('allocation')" :direction="$sorts['allocation'] ?? null" class="whitespace-nowrap">{{ __('Allocation') }}</x-table.heading>
                    <x-table.heading class="whitespace-nowrap">{{ __('WP') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['created_at'] ?? null">{{ __('Created') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($institutions as $institution)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $institution->id }}" class="cursor-pointer hover:bg-gray-50"  wire:click="edit({{ $institution->id }})">
                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5" >
                                <p class="text-gray-600 truncate">
                                    {{ $institution->name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-gray-600 truncate">
                                    {{ $institution->contract }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-gray-600 truncate">
                                    {{ $institution->allocation }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-gray-600 truncate">
                                    @if ($institution->wp)
                                        <x-icon.check class="text-green-500 h-5 w-5"/>
                                    @else
                                        <x-icon.x class="text-red-500 h-5 w-5"/>
                                    @endif
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-gray-600 truncate">
                                    {{ $institution->date_for_humans }}
                                </p>
                            </span>
                        </x-table.cell>
                    </x-table.row>
                    @empty
                    <x-table.row>
                        <x-table.cell colspan="5">
                            <div class="flex justify-center items-center space-x-2">
                                <x-icon.inbox class="h-8 w-8 text-gray-400" />
                                <span class="font-medium py-8 text-gray-400 text-xl">{{ __('Nothing found...') }}</span>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                    @endforelse
                </x-slot>
            </x-table>

            <div>
                {{ $institutions->links() }}
            </div>

        </div>
    </div>

    <!-- Save Institution Modal -->
    <form wire:submit.prevent="save">
        @csrf

        <x-modal.dialog wire:model.defer="showEditModal">
            <x-slot name="title">{{ isset($this->editing->id) ? __('Edit Institution') : __('Create Institution') }}</x-slot>

            <x-slot name="content">
                <x-input.group for="name" label="Name" :error="$errors->first('editing.name')" required>
                    <x-input.text wire:model="editing.name" id="name" placeholder="{{ __('Name') }}" />
                </x-input.group>

                <x-input.group for="contract" label="Contract" :error="$errors->first('editing.contract')" required>
                    <x-input.text wire:model="editing.contract" id="contract" placeholder="{{ __('Contract') }}" />
                </x-input.group>

                <x-input.group for="allocation" label="Allocation" :error="$errors->first('editing.allocation')" required>
                    <x-input.text wire:model="editing.allocation" id="allocation" placeholder="{{ __('Allocation') }}" />
                </x-input.group>

                <x-input.group label="WP" :error="$errors->first('editing.wp')">
                    <x-input.checkbox wire:model="editing.wp" id="wp">{{ __('wp-checkbox') }}</x-input.checkbox>
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showEditModal', false)">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit" class="min-w-24">
                    <div wire:loading.delay><x-icon.loading /></div>
                    <div wire:loading.delay.remove>{{ __('Save') }}</div>
                </x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

</div>