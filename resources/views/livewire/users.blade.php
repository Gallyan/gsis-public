<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Users') }}</h1>

    <div class="py-4 space-y-4">
        <!-- Top Bar -->
        <div class="flex justify-between">
            <div class="w-2/4 flex space-x-4">
                <x-input.text wire:model="filters.search" placeholder="{{ __('Search...') }}" />

                <x-button.link wire:click="toggleShowFilters">@if ($showFilters) Hide @endif Advanced Search...</x-button.link>
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

        <!-- Advanced Search -->
        <div>
            @if ($showFilters)
            <div class="bg-cool-gray-200 p-4 rounded shadow-inner flex relative">
                <div class="w-1/2 pr-2 space-y-4">
                    <x-input.group inline for="filter-role" label="Role">
                        <x-input.select wire:model="filters.role" id="filter-role">
                            <x-slot name="placeholder">
                                Select Role...
                            </x-slot>

                            @foreach (\Spatie\Permission\Models\Role::all() as $role)
                            <option value="{{ $role->id }}">{{ ucfirst( $role->name ) }}</option>
                            @endforeach
                        </x-input.select>
                    </x-input.group>

                    <x-input.group inline for="filter-email" label="Email">
                        <x-input.email wire:model.debounce.500ms="filters.email" id="filter-email" />
                    </x-input.group>
                </div>

                <div class="w-1/2 pl-2 space-y-4">
                    <x-input.group inline for="filter-date-min" label="Created after">
                        <x-input.date wire:model="filters.date-min" id="filter-date-min" placeholder="YYYY-MM-DD" />
                    </x-input.group>

                    <x-input.group inline for="filter-date-max" label="Created before">
                        <x-input.date wire:model="filters.date-max" id="filter-date-max" placeholder="YYYY-MM-DD" />
                    </x-input.group>

                    <div class="pt-5">
                        <x-button.link wire:click="resetFilters" class="absolute right-0 bottom-0 p-4">Reset Filters</x-button.link>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Table -->
        <div class="flex-col space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null" class="w-full">{{ __('Name') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('email')" :direction="$sorts['email'] ?? null">{{ __('Email') }}</x-table.heading>
                    <x-table.heading class="text-left">{{ __('Roles') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['date'] ?? null">{{ __('Created') }}</x-table.heading>
                    <x-table.heading class="text-left">{{ __('Actions') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($users as $user)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $user->id }}">
                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $user->full_name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $user->email }}
                                    @if ( $user->verified === true )
                                    <x-icon.check class="text-green-400" />
                                    @elseif ( $user->verified === false )
                                    <x-icon.x class="text-red-400" />
                                    @endif
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ ucwords( $user->roles->pluck('name')->implode(', ') ) }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $user->date_for_humans }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <x-button.link wire:click="edit({{ $user->id }})" class="text-cool-gray-600 truncate"><x-icon.pencil />{{ __('Edit') }}</x-button.link>
                            </span>
                        </x-table.cell>
                    </x-table.row>
                    @empty
                    <x-table.row>
                        <x-table.cell colspan="6">
                            <div class="flex justify-center items-center space-x-2">
                                <x-icon.inbox class="h-8 w-8 text-cool-gray-400" />
                                <span class="font-medium py-8 text-cool-gray-400 text-xl">No users found...</span>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                    @endforelse
                </x-slot>
            </x-table>

            <div>
                {{ $users->links() }}
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

                <x-input.group for="firstname" label="First Name" :error="$errors->first('editing.firstname')" required>
                    <x-input.text wire:model="editing.firstname" id="firstname" placeholder="First Name" />
                </x-input.group>

                <x-input.group for="birthday" label="Birthday" :error="$errors->first('editing.birthday')">
                    <x-input.date wire:model="editing.birthday" id="birthday" placeholder="YYYY-MM-DD" />
                </x-input.group>

                <x-input.group for="email" label="Email" :error="$errors->first('editing.email')" required>
                    <x-input.email wire:model="editing.email" id="email" />
                </x-input.group>

                <x-input.group for="employer" label="Employer" :error="$errors->first('editing.employer')">
                    <x-input.text wire:model="editing.employer" id="employer" placeholder="Employer" />
                </x-input.group>

                <x-input.group label="Phone" for="phone" :error="$errors->first('editing.phone')">
                    <x-input.phone wire:model.debounce.500ms="editing.phone" id="phone" leading-add-on="" />
                </x-input.group>

                </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="$set('showEditModal', false)">Cancel</x-button.secondary>

                <x-button.primary type="submit">Save</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>

</div>