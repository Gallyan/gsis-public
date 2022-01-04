<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle }} {{ config('app.name', 'Laravel') }}</title>

        <!-- Tailwind -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Baloo+Paaji+2&display=swap" rel="stylesheet">

        @livewireStyles
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@1.2.3/dist/trix.css">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="antialiased font-sans bg-gray-200">

        @env(['production','prod'])
        @else
            <div class="fixed bg-red-500 text-white text-xs font-bold px-10 py-1 transform rotate-45 -right-10 top-4 text-center w-32">{{ ucfirst(env('APP_ENV')) }}</div>
        @endenv

        {{ $slot }}

        @livewireScripts
        <script src="https://unpkg.com/moment"></script>
        <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
        <script src="https://unpkg.com/trix@1.2.3/dist/trix.js"></script>
    </body>
</html>
