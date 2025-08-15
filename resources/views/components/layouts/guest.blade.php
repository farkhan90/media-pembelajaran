<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'SIJAKA - Sistem Interaktif Jelajah Keberagaman Indonesia' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen font-sans antialiased bg-gradient-to-br from-blue-100 to-cyan-100">

    {{-- ============================================= --}}
    {{--           KUNCI UTAMA ADA DI SINI             --}}
    {{-- ============================================= --}}

    {{-- Container ini akan menengahkan semua yang ada di dalamnya --}}
    <div class="min-h-screen flex items-center justify-center">
        {{ $slot }}
    </div>

    {{-- ============================================= --}}

    <x-toast />
</body>

</html>
