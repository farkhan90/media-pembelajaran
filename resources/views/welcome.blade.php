<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selamat Datang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="bg-gray-50 text-black/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl text-center">
                <h1 class="text-4xl font-bold">Selamat Datang di Media Pembelajaran</h1>
                <p class="mt-4">Platform untuk mendukung kegiatan belajar mengajar secara digital.</p>
                <div class="mt-8">
                    <a href="/login" class="inline-block rounded-md bg-indigo-600 px-6 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Masuk ke Aplikasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>