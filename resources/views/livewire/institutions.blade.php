<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Institutions') }}</h1>

    <div class="py-4 space-y-4">
        <!-- Top Bar -->
        <div class="flex justify-between">
            <div class="w-2/4 flex space-x-4">
                <x-input.text wire:model="search" placeholder="{{ __('Search...') }}" />
            </div>

            <div class="space-x-2 flex items-center">
                <x-input.group borderless paddingless for="perPage" label="Per Page">
                    <x-input.select wire:model="perPage" id="perPage">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </x-input.select>
                </x-input.group>

                <x-button.primary wire:click="create"><x-icon.plus/> New</x-button.primary>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-col space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null" class="w-full">{{ __('Name') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('contract')" :direction="$sorts['contract'] ?? null">{{ __('Contract') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('allocation')" :direction="$sorts['allocation'] ?? null">{{ __('Allocation') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['date'] ?? null">{{ __('Created') }}</x-table.heading>
                    <x-table.heading class="text-left">{{ __('Actions') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($institutions as $institution)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $institution->id }}">
                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $institution->name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $institution->contract }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $institution->allocation }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $institution->date_for_humans }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <x-button.link wire:click="edit({{ $institution->id }})" class="text-cool-gray-600 truncate"><x-icon.pencil />{{ __('Edit') }}</x-button.link>
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
                {{ $institutions->links() }}
            </div>

        </div>
    </div>

    <!-- Save User Modal -->
    <form wire:submit.prevent="save">
        <x-modal.dialog wire:model.defer="showEditModal">
            <x-slot name="title">Edit User</x-slot>

            <x-slot name="content">
                <x-input.group for="name" label="Name" :error="$errors->first('editing.name')" required>
                    <x-input.text wire:model="editing.name" id="name" placeholder="Name" />
                </x-input.group>

                <x-input.group for="contract" label="Contract" :error="$errors->first('editing.contract')" required>
                    <x-input.text wire:model="editing.contract" id="contract" placeholder="Contract" />
                </x-input.group>

                <x-input.group for="allocation" label="Allocation" :error="$errors->first('editing.allocation')" required>
                    <x-input.text wire:model="editing.allocation" id="allocation" placeholder="Allocation" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showEditModal', false)">Cancel</x-button.secondary>

                <x-button.primary type="submit">Save</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

</div>