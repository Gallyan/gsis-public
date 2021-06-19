<x-layouts.guest>
    <x-auth.auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-auto h-12" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth.auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- LastName -->
            <div>
                <x-auth.label for="name" :value="__('Last Name')" />

                <x-auth.input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            </div>

            <!-- FirstName -->
            <div class="mt-4">
                <x-auth.label for="firstname" :value="__('First Name')" />

                <x-auth.input id="firstname" class="block mt-1 w-full" type="text" name="firstname" :value="old('firstname')" required autofocus />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-auth.label for="email" :value="__('Email')" />

                <x-auth.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-auth.label for="password" :value="__('Password')" />

                <x-auth.input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-auth.label for="password_confirmation" :value="__('Confirm Password')" />

                <x-auth.input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required />
            </div>

            <div class="flex items-center justify-end mt-4">
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
