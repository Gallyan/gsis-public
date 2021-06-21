<div>
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Users') }}</h1>

    <div class="py-4 space-y-4">
        <!-- Users Table -->
        <div class="flex-col space-y-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading sortable multi-column wire:click="sortBy('name')" :direction="$sorts['name'] ?? null" class="w-full">{{ __('Name') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('email')" :direction="$sorts['email'] ?? null">{{ __('Email') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('roles')" :direction="$sorts['roles'] ?? null">{{ __('Roles') }}</x-table.heading>
                    <x-table.heading sortable multi-column wire:click="sortBy('date')" :direction="$sorts['date'] ?? null">{{ __('Created') }}</x-table.heading>
                    <x-table.heading />
                </x-slot>

                <x-slot name="body">
                    @forelse ($users as $user)
                    <x-table.row wire:loading.class.delay="opacity-50" wire:key="row-{{ $user->id }}">
                        <x-table.cell>
                            <span href="#" class="inline-flex space-x-2 truncate text-sm leading-5">
                                <p class="text-cool-gray-600 truncate">
                                    {{ $user->full_name }}
                                </p>
                            </span>
                        </x-table.cell>

                        <x-table.cell>
                            <span href="#" class="inline-flex space-x-2 truncate text-sm leading-5">
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
                            {{ ucwords( $user->roles_names ) }}
                        </x-table.cell>

                        <x-table.cell>
                            {{ $user->date_for_humans }}
                        </x-table.cell>

                        <x-table.cell>
                            <x-button.link wire:click="edit({{ $user->id }})">{{ __('Edit') }}</x-button.link>
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
</div>