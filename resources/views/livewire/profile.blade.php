<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="My Profile" :modified="$modified"/>

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

            <x-input.group label="Home Address">
                <x-input.group inline label="Street Address" for="hom_adr" :error="$errors->first('user.hom_adr')">
                    <x-input.text wire:model.lazy="user.hom_adr" id="hom_adr" />
                </x-input.group>

                <x-input.group inline class="w-40 pt-4 pr-4" label="Zip&nbsp;code" for="hom_zip" :error="$errors->first('user.hom_zip')">
                    <x-input.text wire:model.lazy="user.hom_zip" id="hom_zip" />
                </x-input.group>

                <x-input.group inline class="w-96 pt-4" label="City" for="hom_cit" :error="$errors->first('user.hom_cit')">
                    <x-input.text wire:model.lazy="user.hom_cit" id="hom_cit" />
                </x-input.group>
            </x-input.group>

            <x-input.group label="Business Address">
                <x-input.group inline label="Institution" for="pro_ins" :error="$errors->first('user.pro_ins')">
                    <x-input.text wire:model.lazy="user.pro_ins" id="pro_ins" />
                </x-input.group>

                <x-input.group inline class="pt-4" label="Street Address" for="pro_adr" :error="$errors->first('user.pro_adr')">
                    <x-input.text wire:model.lazy="user.pro_adr" id="pro_adr" />
                </x-input.group>

                <x-input.group inline class="w-40 pt-4 pr-4" label="Zip&nbsp;code" for="pro_zip" :error="$errors->first('user.pro_zip')">
                    <x-input.text wire:model.lazy="user.pro_zip" id="pro_zip" />
                </x-input.group>

                <x-input.group inline class="w-96 pt-4" label="City" for="pro_cit" :error="$errors->first('user.pro_cit')">
                    <x-input.text wire:model.lazy="user.pro_cit" id="pro_cit" />
                </x-input.group>
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
