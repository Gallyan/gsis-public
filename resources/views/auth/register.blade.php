<x-layouts.guest>
    <x-auth.auth-card>
        <x-slot name="logo">
            <a href="{{ url('/') }}">
                <x-application-logo class="w-auto h-12" />
            </a>
        </x-slot>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- LastName -->
            <div>
                <x-auth.label for="lastname" :value="__('Last Name')" />

                <x-auth.input id="lastname" class="block mt-1 w-full" type="text" name="lastname" :value="old('lastname')" required autofocus />

                <x-auth.auth-validation-errors class="mb-2" :errors="$errors->get('lastname')" />
            </div>

            <!-- FirstName -->
            <div class="mt-2">
                <x-auth.label for="firstname" :value="__('First Name')" />

                <x-auth.input id="firstname" class="block mt-1 w-full" type="text" name="firstname" :value="old('firstname')" required autofocus />

                <x-auth.auth-validation-errors class="mb-2" :errors="$errors->get('firstname')" />
            </div>

            <!-- Email Address -->
            <div class="mt-2">
                <x-auth.label for="email" :value="__('Email')" />

                <x-auth.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />

                <x-auth.auth-validation-errors class="mb-2" :errors="$errors->get('email')" />
            </div>

            <!-- Password -->
            <div class="mt-2">
                <x-auth.label for="password" :value="__('Password')" />
                <x-auth.label :value="__('password-rules')" class="text-xs"/>

                <x-auth.input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />

                <x-auth.auth-validation-errors class="mb-2" :errors="$errors->get('password')" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-2">
                <x-auth.label for="password_confirmation" :value="__('Confirm Password')" />

                <x-auth.input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required />
            </div>

            <!-- CGU -->
            <div class="mt-2">
                <x-auth.input id="cgu" class="mt-1" :checked="old('cgu')"
                                type="checkbox"
                                name="cgu" />
                <span class="text-gray-700 text-xs">{!! __('validate-cgu', ['terms'=>route('terms')]) !!}</span>

                <x-auth.auth-validation-errors class="mb-2" :errors="$errors->get('cgu')" />
            </div>

            <div class="flex items-center justify-end mt-2">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-auth.button class="ml-4">
                    {{ __('Register') }}
                </x-auth.button>
            </div>
        </form>
    </x-auth.auth-card>
</x-layouts.guest>
