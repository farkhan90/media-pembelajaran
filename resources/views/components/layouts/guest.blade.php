<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- ... meta tags dan title ... --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Beri background gradien yang ceria --}}

<body class="min-h-screen font-sans antialiased bg-gradient-to-br from-blue-100 to-cyan-100">
    {{-- Container untuk menengahkan konten --}}
    <div class="min-h-screen flex items-center justify-center p-4">
        {{ $slot }}
    </div>
    <x-toast />
</body>

</html>
