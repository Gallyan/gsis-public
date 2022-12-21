<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Users') }}</h1>

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

                {{-- <x-button.primary wire:click="create"><x-icon.plus/> {{ __('New') }}</x-button.primary> --}}
            </div>
        </div>

        <!-- Advanced Search -->
        <div>
            @if ($showFilters)
            <div class="bg-cool-gray-200 p-4 rounded shadow-inner flex relative">
                <div class="w-1/2 pr-2 space-y-4">
                    <x-input.group inline for="filter-role" label="Role">
                        <x-input.select wire:model="filters.role" id="filter-role" class="w-full">
                            <x-slot name="leadingAddOn">
                                <x-icon.list class="h-5 w-5 text-gray-400" />
                            </x-slot>

                            <x-slot name="placeholder">
                                {{ __('Select Role...') }}
                            </x-slot>

                            @foreach (\Spatie\Permission\Models\Role::all() as $role)
                            <option value="{{ $role->id }}">{{ ucfirst( __($role->name) ) }}</option>
                            @endforeach
                            <option value="none">{{ __('None') }}</option>
                        </x-input.select>
                    </x-input.group>

                    <x-input.group inline for="filter-email" label="Email">
                        <x-input.email wire:model.debounce.500ms="filters.email" id="filter-email" />
                    </x-input.group>

                    <x-input.group inline>
                        <x-input.checkbox wire:model="filters.verified" id="filter-verified" for="filter-verified">
                            {{ __('Verified email') }}
                        </x-input.checkbox>
                    </x-input.group>
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
                    <x-table.heading sortable multi-column wire:click="sortBy('lastname')" :direction="$sorts['lastname'] ?? null" class="w-full">{{ __('Name') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('email')" :direction="$sorts['email'] ?? null" class="whitespace-nowrap">{{ __('Email') }}</x-table.heading>
                    <x-table.heading class="text-left">{{ __('Roles') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('created_at')" :direction="$sorts['created_at'] ?? null">{{ __('Created') }}</x-table.heading>
                </x-slot>

                <x-slot name="body">
                    @forelse ($users as $user)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $user->id }}" class="hover:bg-gray-50">
                        <x-table.cell wire:click="edit({{ $user->id }})" class="cursor-pointer">
                            <span class="inline-flex space-x-2 truncate text-sm leading-5 items-center">
                                <img class="inline-block h-6 w-6 rounded-full text-xs text-gray-500 truncate" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}">
                                <p class="text-gray-600 truncate">
                                    {{ $user->name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell wire:click="edit({{ $user->id }})" class="cursor-pointer">
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-gray-600 truncate">
                                    {{ $user->email }}
                                    @if ( $user->verified === true )
                                    <span title="{{ __('Verified email at') }} {{ $user->email_verified_at }}">
                                        <x-icon.check class="text-green-400" />
                                    </span>
                                    @elseif ( $user->verified === false )
                                    <span title="{{ __('Unverified email') }}">
                                        <x-icon.x class="text-red-400" />
                                    </span>
                                    @endif
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell wire:click="edit({{ $user->id }})" class="cursor-pointer">
                            <span class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-gray-600 truncate">
                                    {{ ucwords( $user->roles->pluck('name')->map(function ($item, $key) { return __($item); })->implode(', ') ) }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell wire:click="edit({{ $user->id }})" class="cursor-pointer">
                            <span
                                class="inline-flex space-x-2 truncate text-sm leading-5"
                                title="{{ $user->created_at }}"
                            >
                                <p class="text-gray-600 truncate">
                                    {{ $user->date_for_humans }}
                                </p>
                            </span>
                        </x-table.cell>
                    </x-table.row>
                    @empty
                    <x-table.row>
                        <x-table.cell colspan="4">
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
                {{ $users->links() }}
            </div>

        </div>
    </div>

    <!-- Save User Modal -->
    <form wire:submit.prevent="save">
        @csrf

        <x-modal.dialog wire:model.defer="showEditModal">
            <x-slot name="title">{{ isset($this->editing->id) ? __('Edit User') : __('Create User') }}</x-slot>

            <x-slot name="content">
                <x-input.group for="lastname" label="Last Name" :error="$errors->first('editing.lastname')" required>
                    <x-input.text wire:model="editing.lastname" id="lastname" placeholder="{{ __('Last Name') }}" leadingIcon="identity" />
                </x-input.group>

                <x-input.group for="firstname" label="First Name" :error="$errors->first('editing.firstname')" required>
                    <x-input.text wire:model="editing.firstname" id="firstname" placeholder="{{ __('First Name') }}" leadingIcon="identity" />
                </x-input.group>

                <x-input.group for="birthday" label="Birthday" :error="$errors->first('editing.birthday')" required>
                    <x-input.date wire:model="editing.birthday" id="birthday" placeholder="{{ __('YYYY-MM-DD') }}" />
                </x-input.group>

                <x-input.group for="email" label="Email" :error="$errors->first('editing.email')" helpText="{{ ( isset($this->editing->getDirty()['email']) && $this->editing->password ) ? __('If you change the email, user will receive a new verification email, and will not be able to access the site features until new email validation.') : ( isset($this->editing->id) ? '' : __('A validation email will be sent to newly created user.') ) }}" required>
                    <x-input.email wire:model="editing.email" id="email" :verified="$this->editing->verified" />
                </x-input.group>

                <x-input.group for="employer" label="Employer" :error="$errors->first('editing.employer')">
                    <x-input.text wire:model="editing.employer" id="employer" placeholder="{{ __('Employer') }}" leadingIcon="company" />
                </x-input.group>

                <x-input.group label="Phone" for="phone" :error="$errors->first('editing.phone')">
                    <x-input.phone wire:model.debounce.500ms="editing.phone" id="phone" />
                </x-input.group>

                @can('manage-roles')
                <x-input.group label="Rôles" :error="$errors->first('selectedroles.*')">
                    <div class="flex flex-row pt-2">
                    @foreach ( $Roles as $role )
                        @if ( $role !== "admin" || auth()->user()->can('manage-admin') )
                        <div class="flex-1 text-gray-700">
                            <input
                                type="checkbox"
                                id="{{ $role  }}"
                                name="selectedroles[]"
                                value="1"
                                wire:model="selectedroles.{{$role}}"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            >
                            <label for="{{ $role }}" class="font-medium text-gray-700">{{ __($role) }}</label>
                        </div>
                        @endif
                    @endforeach
                    </div>
                </x-input.group>
                @endcan
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