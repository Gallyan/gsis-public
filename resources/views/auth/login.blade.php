<x-layouts.guest>
    <x-auth.auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-auto h-12" />
            </a>
        </x-slot>

        <x-slot name="footer">
            <div class="flex items-center justify-center mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('register') }}">
                    {{ __('Create an account') }}
                </a>
            </div>
            <div class="flex flex-row flex-wrap justify-center mt-4">
                <a class="p-2 text-xs text-gray-600 hover:text-gray-900 hover:underline text-center" href="{{ route('terms') }}">
                    {{ __('Terms of use') }}
                </a>
            </div>
        </x-slot>

        <!-- Session Status -->
        <x-auth.auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-auth.auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-auth.label for="email" :value="__('Email')" />

                <x-auth.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-auth.label for="password" :value="__('Password')" />

                <x-auth.input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                    <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-auth.button class="ml-3">
                    {{ __('Log in') }}
                </x-auth.button>
            </div>
        </form>
    </x-auth.auth-card>
</x-layouts.guest>
