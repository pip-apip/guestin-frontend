<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        @stack('styles')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 flex items-center justify-center p-0 lg:p-4">
        {{ $slot }}

        @stack('scripts')
        @fluxScripts
    </body>
</html>
