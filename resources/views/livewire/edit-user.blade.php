<div class="relative">
    <form wire:submit.prevent="save" wire:reset.prevent="init">
        @csrf

        <x-stickytopbar title="{{ $user->name }}" :modified="$modified"/>

        <div class="mt-6 sm:mt-5">
            <x-input.group label="First Name" for="firstname" :error="$errors->first('user.firstname')" required borderless>
                <x-input.text wire:model.debounce.500ms="user.firstname" id="firstname" leadingIcon="identity" />
            </x-input.group>

            <x-input.group label="Last Name" for="lastname" :error="$errors->first('user.lastname')" required>
                <x-input.text wire:model.debounce.500ms="user.lastname" id="lastname" leadingIcon="identity" />
            </x-input.group>

            <x-input.group label="Birthday" for="birthday" :error="$errors->first('user.birthday')" required>
                <x-input.date wire:model="user.birthday" id="birthday" placeholder="{{ __('YYYY-MM-DD') }}" required />
            </x-input.group>

            <x-input.group label="Birthplace" for="birthplace" :error="$errors->first('user.birthplace')" required>
                <x-input.text wire:model.debounce.500ms="user.birthplace" id="birthplace" leadingIcon="location" />
            </x-input.group>

            <x-input.group label="Email" for="email" :error="$errors->first('user.email')" helpText="{{ isset($user->getDirty()['email']) ? __('helptext-user-change-email') : '' }}" required>
                <x-input.email wire:model.debounce.500ms="user.email" id="email" :verified="$user->verified" />
            </x-input.group>

            <x-input.group label="Employer" for="employer" :error="$errors->first('user.employer')">
                <x-input.text wire:model.lazy="user.employer" id="employer" leadingIcon="company" />
            </x-input.group>

            <x-input.group label="Phone" for="phone" :error="$errors->first('user.phone')">
                <x-input.phone wire:model.debounce.500ms="user.phone" id="phone" />
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

            <x-input.group label="Home Address" helpText="{{ $isAuthManager ? '' : 'change-hom-address' }}">
                <x-input.group inline class="w-136" label="Street Address" for="hom_adr" :error="$errors->first('user.hom_adr')">
                    <x-input.text wire:model.lazy="user.hom_adr" id="hom_adr" />
                </x-input.group>

                <div class="inline-flex" >
                    <x-input.group inline class="w-40 pt-4 pr-4" label="Zip code" for="hom_zip" :error="$errors->first('user.hom_zip')">
                        <x-input.text wire:model.lazy="user.hom_zip" id="hom_zip" />
                    </x-input.group>

                    <x-input.group inline class="w-96 pt-4" label="City" for="hom_cit" :error="$errors->first('user.hom_cit')">
                        <x-input.text wire:model.lazy="user.hom_cit" id="hom_cit" />
                    </x-input.group>
                </div>
            </x-input.group>

            <x-input.group label="Business Address" helpText="{{ $isAuthManager ? '' : 'change-pro-address' }}">
                <x-input.group inline class="w-136" label="pro-ins" for="pro_ins" :error="$errors->first('user.pro_ins')">
                    <x-input.text wire:model.lazy="user.pro_ins" id="pro_ins" />
                </x-input.group>

                <x-input.group inline class="w-136 pt-4" label="Street Address" for="pro_adr" :error="$errors->first('user.pro_adr')">
                    <x-input.text wire:model.lazy="user.pro_adr" id="pro_adr" />
                </x-input.group>

                <div class="inline-flex" >
                    <x-input.group inline class="w-40 pt-4 pr-4" label="Zip code" for="pro_zip" :error="$errors->first('user.pro_zip')">
                        <x-input.text wire:model.lazy="user.pro_zip" id="pro_zip" />
                    </x-input.group>

                    <x-input.group inline class="w-96 pt-4" label="City" for="pro_cit" :error="$errors->first('user.pro_cit')">
                        <x-input.text wire:model.lazy="user.pro_cit" id="pro_cit" />
                    </x-input.group>
                </div>
            </x-input.group>

            <x-input.group label="Documents" helpText="helptext-documents">
                @if (!empty($user->documents))
                <ul role="list">
                @foreach( $user->documents as $document )
                    <li class="@if ($loop->first) mb-4 @else my-4 @endif flex border-dashed border-2 border-gray-300 rounded-md p-2">
                        <x-icon.document class="w-10 h-10 text-gray-500" />
                        <div class="mx-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <a href="{{ route( 'download', $document->id ) }}" target="_blank">{{ $document->name }}</a> <span class="text-sm text-gray-500">({{ $document->sizeForHumans }}) {{ __('Added :date',[ 'date' => $document->created_at->diffForHumans() ]) }}</span>
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ __($document->type) }}
                                @if($document->from && $document->to)
                                    {{ __('valid from :from to :to',
                                        [
                                            'from' => $document->from->format('d/m/Y'),
                                            'to' => $document->to->format('d/m/Y'),
                                        ]) }}
                                @elseif($document->from)
                                    {{ __('valid from :from',
                                        [
                                            'from' => $document->from->format('d/m/Y'),
                                        ]) }}
                                @elseif($document->to)
                                    {{ __('valid until :to',
                                        [
                                            'to' => $document->to->format('d/m/Y'),
                                        ]) }}
                                @endif
                            </p>
                            @if ($document->to && $document->to->lte(Illuminate\Support\Carbon::now()))
                            <p class="text-sm text-red-500 font-semibold">
                                <x-icon.warning class="mr-2 flex-shrink-0 h-8 w-8 text-red-400" />
                                {{ __('Document has expired') }}
                            </p>
                            @endif
                        </div>
                        <x-icon.pencil class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer" wire:click="editDoc({{ $document->id }})" />
                        <x-icon.trash class="ml-3 mr-1 w-6 h-6 text-gray-500 cursor-pointer" wire:click="confirm({{ $document->id }})" />
                    </li>
                @endforeach
                </ul>
                @endif
                <x-button.secondary wire:click="show_modal">
                    <x-icon.document-add />{{ __('Add document') }}
                </x-button.secondary>
            </x-input.group>

            <x-input.group label="Language" for="locale" :error="$errors->first('user.locale')">
                <x-input.radio id="locale" wire:model="user.locale" :keylabel="$languages" />
            </x-input.group>

            <x-input.group label="Password">
                <x-button.secondary wire:click="reset_password">
                    <span wire:loading.remove.delay.shorter wire:target="reset_password">{{ __('Send reset password link by email') }}</span>
                    <span wire:loading.delay.shorter wire:target="reset_password" class="invisible">{{ __('Send reset password link by email') }}</span>
                    <div wire:loading.delay.shorter wire:target="reset_password" class="w-full float-left -mt-6"><x-icon.loading class="mx-auto w-6 h-6"/></div>
                </x-button.secondary>
                <div class="pt-4">
                    <x-notify-message event='notify-sent-ok' color='text-green-600'>{{ __(Password::RESET_LINK_SENT) }}</x-notify-message>
                    <x-notify-message event='notify-sent-error' color='text-red-600'>{{ __($errors->first('password')) }}</x-notify-message>
                </div>
            </x-input.group>

            @can('manage-roles')
            <x-input.group label="Roles" :error="$errors->first('selectedroles.*')">
                <fieldset class="space-y-5">

                @foreach ( $Roles as $role )
                @if ( $role !== "admin" || auth()->user()->can('manage-admin') )

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

                @endif
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

            @can('manage-admin')
            <x-input.group label="Last login">
                <span class="text-sm font-medium text-gray-700">
                @if  ($user->last_login_at )
                    {{ __('Last login on :at', ['at'=>\Illuminate\Support\Carbon::parse($user->last_login_at)->translatedFormat(__('lastlog-dt'))]) }}
                    @if  ($user->last_login_ip )
                        {{ __('from IP :from', ['from'=>$user->last_login_ip]) }}
                    @endif
                @else
                    {{ __('The user has never logged in.')}}
                @endif
                </span>
            </x-input.group>
            @endcan

        </div>
    </form>

    <!-- Confirm file deletion //-->
    <x-modal.confirmation wire:model.defer="showDeleteModal">
        <x-slot name="title">
            {{ __('Delete document') }}
        </x-slot>

        <x-slot name="content">

        <x-input.group>
            <span class="text-cool-gray-900">
                {{ __('Do you really want to delete document') }} <span class="italic font-bold whitespace-nowrap">{{ $delDocName }}</span>&nbsp;?
            </span>
        </x-input.group>

        </x-slot>

        <x-slot name="footer">
            <x-button.secondary wire:click="close_modal">{{ __('Cancel') }}</x-button.secondary>

            <x-button class="bg-red-600 hover:bg-red-500 active:bg-red-700" wire:click="del_doc({{ $showDeleteModal }})">{{ __('Delete') }}</x-button>
        </x-slot>
    </x-modal.dialog>

    <!-- Add document Modal -->
    <form wire:submit.prevent="save_doc">
        @csrf

        <x-modal.dialog wire:model.defer="showModal">
            <x-slot name="title">
                @if (isset($doc['id']))
                    {{ __('Edit document') }}
                @else
                    {{ __('Add document') }}
                @endif
            </x-slot>

            <x-slot name="content">
                @if (isset($doc['id']))
                <x-input.group for="file" label="File">
                    <p class="text-sm font-medium text-gray-700">
                        <a href="{{ route( 'download', $document->id ) }}" target="_blank">
                            {{ $doc['name'] }}
                            <x-icon.download class="text-gray-500" />
                        </a>
                    </p>
                </x-input.group>
                @else
                <x-input.group for="file" label="File" :error="$errors->first('doc.file')" required>
                    <x-input.filepond
                        wire:model="doc.file"
                        id="file"
                        inputname="file"
                        maxFileSize="10MB"
                        acceptedFileTypes="[ 'image/*', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip']"
                    />
                </x-input.group>
                @endif

                <x-input.group for="type" label="Type" :error="$errors->first('doc.type')" required>
                    <x-input.select wire:model="doc.type" id="type" placeholder="{{ __('Select Type...') }}" class="w-full">
                        @foreach (collect(\App\Models\User::DOCTYPE)->map(fn($i,$k)=>__($i))->sort() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </x-input.select>
                </x-input.group>

                <x-input.group for="name" label="Name" :error="$errors->first('doc.name')" required>
                    <x-input.text wire:model.debounce.500ms="doc.name" id="name" placeholder="{{ __('Name') }}" />
                </x-input.group>

                <x-input.group label="Dates" innerclass="sm:flex" helpText="helptext-expirationdates" >
                    <x-input.group label="Valid from date" for="doc.from" :error="$errors->first('doc.from')" class="sm:w-1/2" inline>
                        <x-input.date wire:model="doc.from" id="from" placeholder="{{ __('YYYY-MM-DD') }}" />
                    </x-input.group>
                    <x-input.group label="Expiration date" for="doc.to" :error="$errors->first('doc.to')" inline>
                        <x-input.date wire:model="doc.to" id="to" placeholder="{{ __('YYYY-MM-DD') }}" />
                    </x-input.group>
                </x-input.group>

            </x-slot>

            <x-slot name="footer">
                <x-button.secondary wire:click="close_modal">{{ __('Cancel') }}</x-button.secondary>

                <x-button.primary wire:loading.attr="disabled" type="submit">
                @if (isset($doc['id']))
                    {{ __('Save') }}
                @else
                    {{ __('Add') }}
                @endif
            </x-button.primary>
            </x-slot>
        </x-modal.dialog>
    </form>
</div>
