<div>
    <h1 class="text-2xl font-semibold text-gray-900">Profile</h1>

    <form wire:submit.prevent="save">
        @csrf

        <div class="mt-6 sm:mt-5">
            <x-input.group label="First Name" for="firstname" :error="$errors->first('user.firstname')" required>
                <x-input.text wire:model.debounce.500ms="user.firstname" id="firstname" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Last Name" for="name" :error="$errors->first('user.name')" required>
                <x-input.text wire:model.debounce.500ms="user.name" id="name" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Birthday" for="birthday" :error="$errors->first('user.birthday')" required>
                <x-input.date wire:model="user.birthday" id="birthday" placeholder="YYYY-MM-DD" required />
            </x-input.group>

            <x-input.group label="Email" for="email" :error="$errors->first('user.email')" required>
                <x-input.email wire:model.debounce.500ms="user.email" id="email" leading-add-on="" :verified="auth()->user()->verified"/>
            </x-input.group>

            <x-input.group label="Employer" for="employer" :error="$errors->first('user.employer')">
                <x-input.text wire:model.lazy="user.employer" id="employer" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Phone" for="phone" :error="$errors->first('user.phone')">
                <x-input.phone wire:model.debounce.500ms="user.phone" id="phone" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Photo" for="photo" :error="$errors->first('upload')">
                <x-input.file-upload wire:model="upload" id="photo">
                    <span class="h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                        @if ($upload)
                            <img src="{{ $upload->temporaryUrl() }}" alt="Profile Photo">
                        @else
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="Profile Photo">
                        @endif
                    </span>
                </x-input.file-upload>
            </x-input.group>

            @can('manage-users')
            <x-input.group label="Roles" for="roles">
                <div class="flex">
                    {{ ucwords( auth()->user()->roles_names ) }}
                </div>
            </x-input.group>
            @endcan

        </div>

        <div class="mt-2 border-t border-gray-200 pt-5">
            <div class="space-x-3 flex justify-end items-center">
                <span x-data="{ open: false }" x-init="
                        @this.on('notify-saved', () => {
                            if (open === false) setTimeout(() => { open = false }, 2500);
                            open = true;
                        })
                    " x-show.transition.out.duration.1000ms="open" style="display: none;" class="text-gray-500">Saved!</span>

                <span class="inline-flex rounded-md shadow-sm">
                    <button type="button" class="py-2 px-4 border border-gray-300 rounded-md text-sm leading-5 font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out">
                        Cancel
                    </button>
                </span>

                <span class="inline-flex rounded-md shadow-sm">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                        Save
                    </button>
                </span>
            </div>
        </div>
    </form>
</div>
