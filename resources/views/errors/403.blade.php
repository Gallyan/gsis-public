<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('403 error') }} - {{ config('app.name', 'Gsis 2') }}</title>

        <!-- Tailwind -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="antialiased font-sans h-full">

        <div class="bg-white min-h-full px-4 py-16 sm:px-6 sm:py-24 md:grid md:place-items-center lg:px-8">
            <div class="max-w-max mx-auto">
                <div class="flex-shrink-0 flex justify-center">
                    <a href="/" class="inline-flex mb-6">
                        <x-application-logo class="w-auto h-12" />
                    </a>
                </div>
                <main class="flex">
                    <p class="text-4xl font-extrabold text-indigo-600 sm:text-5xl">403</p>
                    <div class="sm:ml-6">
                        <div class="sm:border-l sm:border-gray-200 sm:pl-6">
                            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl">{{ __('Forbidden') }}</h1>
                            <p class="mt-1 text-base text-gray-500">{{ __('This action is unauthorized.') }}</p>
                        </div>
                    </div>
                </main>
                <div class="flex-shrink-0 flex justify-center">
                    <div class="mt-10 flex space-x-3 sm:border-l sm:border-transparent sm:pl-6">
                        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"> {{ __('Home') }} </a>
                        <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer"> {{ __('Back') }} </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
