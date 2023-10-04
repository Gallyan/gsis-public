<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Institutions') }}</h1>

    <div class="py-4 space-y-4">
        <!-- Top Bar -->
        <div class="flex justify-between flex-wrap gap-2 print:hidden">
            <div class="flex space-x-2">
                <x-input.text wire:model="search" placeholder="{{ __('Search...') }}" leadingIcon="magnifier" />
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
                    <x-table.heading sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null">{{ __('Name') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('contract')" :direction="$sorts['contract'] ?? null">{{ __('Contract') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('allocation')" :direction="$sorts['allocation'] ?? null" class="whitespace-nowrap">{{ __('Allocation') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('from')" :direction="$sorts['from'] ?? null">{{ __('Start') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('to')" :direction="$sorts['to'] ?? null">{{ __('End') }}</x-table.heading>
                    <x-table.heading>{{ __('WP') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($institutions as $institution)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $institution->id }}" class="cursor-pointer hover:bg-gray-100 {{ $loop->even ? 'bg-cool-gray-50' : '' }}"  wire:click="edit({{ $institution->id }})">
                        <x-table.cell class="whitespace-normal">
                            {{ $institution->name }}
                        </x-table.cell>

                        <x-table.cell class="whitespace-normal">
                            {{ $institution->contract }}
                        </x-table.cell>

                        <x-table.cell class="whitespace-normal">
                            {{ $institution->allocation }}
                        </x-table.cell>

                        <x-table.cell class="whitespace-nowrap">
                            {{ $institution->fromFormatted }}
                        </x-table.cell>

                        <x-table.cell class="whitespace-nowrap">
                            {{ $institution->toFormatted }}
                        </x-table.cell>

                        <x-table.cell class="whitespace-nowrap">
                            @if ($institution->wp)
                                <x-icon.check class="text-green-500 h-5 w-5"/>
                            @else
                                <x-icon.x class="text-red-500 h-5 w-5"/>
                            @endif
                        </x-table.cell>
                    </x-table.row>
                    @empty
                    <x-table.row>
                        <x-table.cell colspan="6">
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
                <x-input.group paddingless borderless class="sm:py-1" for="name" label="Name" :error="$errors->first('editing.name')" required>
                    <x-input.text wire:model="editing.name" id="name" placeholder="{{ __('Name') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="contract" label="Contract" :error="$errors->first('editing.contract')" required>
                    <x-input.text wire:model="editing.contract" id="contract" placeholder="{{ __('Contract') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="allocation" label="Allocation" :error="$errors->first('editing.allocation')" required>
                    <x-input.text wire:model="editing.allocation" id="allocation" placeholder="{{ __('Allocation') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" label="WP" :error="$errors->first('editing.wp')">
                    <p class="block text-sm font-medium leading-5 text-gray-700">{{ __('wp-checkbox') }}</p>
                    <x-input.toggle
                        id="wp"
                        wire:model="editing.wp"
                        :before="'Non'"
                        :after="'Oui'"
                        :choice="$editing->wp"
                        class="inline-flex mr-4"
                    />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="from" label="Start date" :error="$errors->first('editing.from')" >
                    <x-input.date wire:model.lazy="editing.from" id="from" placeholder="{{ __('YYYY-MM-DD') }}" />
                </x-input.group>

                <x-input.group paddingless borderless class="sm:py-1" for="to" label="End date" :error="$errors->first('editing.to')" >
                    <x-input.date wire:model.lazy="editing.to" id="to" placeholder="{{ __('YYYY-MM-DD') }}" />
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