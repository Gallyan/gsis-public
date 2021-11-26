<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gsis 2') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        @env(['preprod','local'])
            <div class="fixed bg-red-500 text-white text-sm font-bold px-10 py-1 transform rotate-45 -right-10 top-4 text-center w-32">{{ ucfirst(env('APP_ENV')) }}</div>
        @endenv

        <div>
            {{ $slot }}
        </div>
    </body>
</html>
