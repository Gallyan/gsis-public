<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="Profile" />

        <div class="mt-6 sm:mt-5">
            <x-input.group label="First Name" for="firstname" :error="$errors->first('user.firstname')" required  borderless>
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
    </form>
</div>
