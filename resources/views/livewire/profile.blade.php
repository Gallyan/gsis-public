<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="Profile" :modified="$modified"/>

        <div class="mt-6 sm:mt-5">
            <x-input.group label="First Name" for="firstname" :error="$errors->first('user.firstname')" required borderless>
                <x-input.text wire:model.debounce.500ms="user.firstname" id="firstname">
                    <x-slot name="leadingAddOn"><x-icon.identity /></x-slot>
                </x-input.text>
            </x-input.group>

            <x-input.group label="Last Name" for="name" :error="$errors->first('user.name')" required>
                <x-input.text wire:model.debounce.500ms="user.name" id="name">
                    <x-slot name="leadingAddOn"><x-icon.identity /></x-slot>
                </x-input.text>
            </x-input.group>

            <x-input.group label="Birthday" for="birthday" :error="$errors->first('user.birthday')" required>
                <x-input.date wire:model="user.birthday" id="birthday" placeholder="YYYY-MM-DD" required />
            </x-input.group>

            <x-input.group label="Email" for="email" :error="$errors->first('user.email')" helpText="{{ isset($this->user->getDirty()['email']) ? __('If you change your email, you will receive a new verification email, and you will not be able to access the site features until you validate the new email.') : '' }}" required>
                <x-input.email wire:model.debounce.500ms="user.email" id="email" leading-add-on="" :verified="$this->user->verified" />
            </x-input.group>

            <x-input.group label="Employer" for="employer" :error="$errors->first('user.employer')">
                <x-input.text wire:model.lazy="user.employer" id="employer">
                    <x-slot name="leadingAddOn"><x-icon.company /></x-slot>
                </x-input.text>
            </x-input.group>

            <x-input.group label="Phone" for="phone" :error="$errors->first('user.phone')">
                <x-input.phone wire:model.debounce.500ms="user.phone" id="phone" leading-add-on="" />
            </x-input.group>

            <x-input.group label="Photo" for="photo" :error="$errors->first('upload')" innerclass="flex space-x-6">
                <div class="h-16 w-16 rounded-full overflow-hidden flex-none">
                    @if ( ! empty($upload) && $upload->isPreviewable())
                        <img src="{{ $upload->temporaryUrl() }}" alt="{{ __('Profile Photo') }}">
                    @else
                        <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ __('Profile Photo') }}">
                    @endif
                </div>
                <x-input.filepond
                    wire:model="upload"
                    id="photo"
                    inputname="photo"
                    class="flex-1"
                    maxFileSize="1MB"
                    acceptedFileTypes="['image/*']"
                />
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
