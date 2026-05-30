<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f766e">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Qbus">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="format-detection" content="telephone=no">

        @php
            $faviconVersion = @filemtime(public_path('favicon.ico')) ?: time();
            $appleTouchVersion = @filemtime(public_path('apple-touch-icon.png')) ?: time();
            $manifestVersion = @filemtime(public_path('manifest.json')) ?: time();
        @endphp

        <link rel="manifest" href="/manifest.json?v={{ $manifestVersion }}">
        <link rel="icon" type="image/svg+xml" href="/favicon.svg?v={{ $faviconVersion }}">
        <link rel="icon" type="image/x-icon" href="/favicon.ico?v={{ $faviconVersion }}" sizes="any">
        <link rel="shortcut icon" href="/favicon.ico?v={{ $faviconVersion }}">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v={{ $appleTouchVersion }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'Qbus') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
