<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        @if (filled($brand['description']))
            <meta name="description" content="{{ $brand['description'] }}">
        @endif
        @if ($brand['favicon'] !== null)
            <link rel="icon" href="{{ $brand['favicon'] }}">
        @endif
        <script src="/theme-init.js"></script>
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        <x-inertia::head>
            <title>{{ $brand['name'] }}</title>
        </x-inertia::head>
    </head>
    <body>
        <x-inertia::app />
    </body>
</html>
