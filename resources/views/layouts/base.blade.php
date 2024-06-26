<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{!! $pageTitle ?? '' !!} {{ config('app.name', 'Gsis 2') }}</title>

        <!-- Tailwind -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">

        @livewireStyles

        @stack('stylesheets')

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="antialiased font-sans">

        @env(['production','prod'])
        @else
            <div class="fixed bg-red-500 text-white text-xs font-bold px-10 py-1 rotate-45 -right-10 top-4 text-center w-40 z-50">{{ ucfirst(App::environment()) }}</div>
        @endenv

        {{ $slot }}

        @livewireScripts

        @stack('scripts')

    </body>
</html>
