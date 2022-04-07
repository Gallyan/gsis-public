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
                        <img src="{{ $user->avatarUrl() }}" alt="{{ __('Profile Photo') }}">
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

                <x-input.group inline class="w-40 pt-4 pr-4" label="Zip code" for="hom_zip" :error="$errors->first('user.hom_zip')">
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

                <x-input.group inline class="w-40 pt-4 pr-4" label="Zip code" for="pro_zip" :error="$errors->first('user.pro_zip')">
                    <x-input.text wire:model.lazy="user.pro_zip" id="pro_zip" />
                </x-input.group>

                <x-input.group inline class="w-96 pt-4" label="City" for="pro_cit" :error="$errors->first('user.pro_cit')">
                    <x-input.text wire:model.lazy="user.pro_cit" id="pro_cit" />
                </x-input.group>
            </x-input.group>

            <x-input.group label="Documents">
                @if (!empty($user->documents))
                <ul role="list" class="divide-y divide-gray-200">
                @foreach( $user->documents as $document )
                    <li class="@if ($loop->first) pb-4 @else py-4 @endif flex">
                        <x-icon.document class="w-10 h-10 text-gray-500" />
                        <div class="mx-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <a href="{{-- $document->download --}}" target="_blank">{{ $document->name }}</a> <span class="text-sm text-gray-500">({{ $document->sizeForHumans }}) {{ __('Added :date',[ 'date' => $document->created_at->diffForHumans() ]) }}</span>
                            </p>
                            <p class="text-sm text-gray-500">{{ __($document->type) }}</p>
                        </div>
                        <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer" wire:click="del_doc({{ $document->id }})"/>
                    </li>
                @endforeach
                </ul>
                @endif
                <x-button.secondary wire:click="$set('showModal', true)">
                    <x-icon.document-add />{{ __('Add document') }}
                </x-button.secondary>
            </x-input.group>

            @can('manage-roles')
            <x-input.group label="Roles" :error="$errors->first('selectedroles.*')">
                <fieldset class="space-y-5">

                @foreach ( $Roles as $role )

                <div class="relative flex items-start">

                    <div class="flex items-center h-5">

                        <input
                            type="checkbox"
                            id="{{ $role  }}"
                            name="selectedroles[]"
                            value="1"
                            wire:model="selectedroles.{{$role}}"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        >

                    </div>

                    <div class="ml-3 text-sm">
                        <label for="{{ $role }}" class="font-medium text-gray-700">{{ __($role) }}</label>
                        <p class="text-gray-500">{{ __($role.'-description') }}</p>
                    </div>

                </div>

                @endforeach
                </fieldset>
            </x-input.group>
            @else
            <x-input.group label="Roles">
                <span class="font-medium text-gray-700">
                    {{ $user->RolesNames }}
                </span>
            </x-input.group>
            @endcan

        </div>
    </form>

    <!-- Add document Modal -->
    <form wire:submit.prevent="save_doc">
        @csrf

        <x-modal.dialog wire:model.defer="showModal">
            <x-slot name="title">
                {{ __('Add document') }}
            </x-slot>

            <x-slot name="content">
                <x-input.group for="file" label="File" :error="$errors->first('doc.file')" required>
                    <x-input.filepond
                        wire:model="doc.file"
                        id="file"
                        inputname="file"
                        maxFileSize="2MB"
                        acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                    />
                </x-input.group>

                <x-input.group for="type" label="Type" :error="$errors->first('doc.type')" required>
                    <x-input.select wire:model="doc.type" id="type" placeholder="{{ __('Select Type...') }}" class="w-full">
                        <option value="id">{{ __('id') }}</option>
                        <option value="bank">{{ __('RIB') }}</option>
                        <option value="passport">{{ __('passport') }}</option>
                        <option value="driver">{{ __('driver') }}</option>
                    </x-input.select>
                </x-input.group>

                <x-input.group for="name" label="Name" :error="$errors->first('doc.name')" required>
                    <x-input.text wire:model.debounce.500ms="doc.name" id="name" placeholder="{{ __('Name') }}" />
                </x-input.group>
            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_modal">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary type="submit">@lang('Add')</x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>
</div>
