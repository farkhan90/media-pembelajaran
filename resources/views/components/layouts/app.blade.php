<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <title>{{ 'Dashboard - SIJAKA' }}</title>

    <script src="https://cdn.tiny.cloud/1/ku3nhu0lnokxa4x7tgfmcpufpzw4cd7pvl3ltqsq70sox4xf/tinymce/8/tinymce.min.js"
        referrerpolicy="origin" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            {{-- Menggunakan logo SIJAKA Anda --}}
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" class="w-10 h-10" alt="Logo SIJAKA" />
                <div class="text-primary font-bold text-lg">SIJAKA</div>
            </div>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <div class="flex items-center gap-3 pt-4 pl-4">
                <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" class="w-10 h-10" alt="Logo SIJAKA" />
                <div class="text-black font-bold text-lg">SIJAKA</div>
            </div>

            {{-- MENU --}}
            <x-menu activate-by-route>

                {{-- User --}}
                @if ($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="nama" sub-value="email" no-separator no-hover
                        class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <x-dropdown>
                                <x-slot:trigger>
                                    <x-button icon="o-cog-6-tooth" class="btn btn-circle btn-ghost" />
                                </x-slot:trigger>
                                {{-- Bungkus dengan <div> atau <li> yang memiliki event listener --}}
                                <x-menu-item>
                                    <x-theme-toggle title="Theme" />
                                </x-menu-item>
                                <a href="{{ route('logout') }}" wire:navigate.prevent>
                                    <x-button label="Logout" icon="o-arrow-left-on-rectangle"
                                        class="btn-sm btn-outline btn-error" responsive />
                                </a>
                            </x-dropdown>
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Beranda" icon="o-home" link="/dashboard" />
                <x-menu-item title="Halaman Utama" icon="o-sparkles" link="{{ route('selamat-datang') }}"
                    wire:navigate />
                {{-- ======================================================= --}}
                {{--                      MENU KHUSUS ADMIN                  --}}
                {{-- ======================================================= --}}
                @if (auth()->user()->role === 'Admin')
                    <x-menu-separator />

                    <x-menu-sub title="Manajemen Konten" icon="o-pencil-square">
                        <x-menu-item title="Modul (Pulau)" icon="o-map-pin" link="{{ route('modul.index') }}"
                            wire:navigate />
                        <x-menu-item title="Ujian (Pilgan)" icon="o-academic-cap" link="{{ route('ujian.index') }}"
                            wire:navigate />
                        <x-menu-item title="Kuis (Menjodohkan)" icon="o-puzzle-piece" link="{{ route('kuis.index') }}"
                            wire:navigate />
                    </x-menu-sub>

                    <x-menu-sub title="Manajemen Data Master" icon="o-circle-stack">
                        <x-menu-item title="Sekolah" icon="o-building-office-2" link="{{ route('sekolah.index') }}"
                            wire:navigate />
                        <x-menu-item title="Kelas" icon="o-table-cells" link="{{ route('kelas.index') }}"
                            wire:navigate />
                        <x-menu-item title="User" icon="o-users" link="{{ route('users.index') }}" wire:navigate />
                    </x-menu-sub>

                    <x-menu-sub title="Laporan & Siswa" icon="o-chart-bar">
                        <x-menu-item title="Siswa per Kelas" icon="o-identification" link="{{ route('siswa.manage') }}"
                            wire:navigate />
                        <x-menu-item title="Hasil Ujian" icon="o-chart-bar-square" link="{{ route('ujian.hasil') }}"
                            wire:navigate />
                        <x-menu-item title="Hasil Kuis" icon="o-presentation-chart-line"
                            link="{{ route('kuis.hasil') }}" wire:navigate />
                        <x-menu-item title="Jawaban Esai" icon="o-document-text" link="{{ route('laporan.esai') }}"
                            wire:navigate />
                    </x-menu-sub>

                    {{-- ======================================================= --}}
                    {{--                       MENU KHUSUS GURU                  --}}
                    {{-- ======================================================= --}}
                @elseif(auth()->user()->role === 'Guru')
                    <x-menu-separator />
                    <x-menu-item title="Kelola Siswa" icon="o-identification" link="{{ route('siswa.manage') }}"
                        wire:navigate />

                    <x-menu-sub title="Laporan Siswa" icon="o-chart-bar">
                        <x-menu-item title="Hasil Ujian" icon="o-chart-bar-square" link="{{ route('ujian.hasil') }}"
                            wire:navigate />
                        <x-menu-item title="Hasil Kuis" icon="o-presentation-chart-line"
                            link="{{ route('kuis.hasil') }}" wire:navigate />
                        <x-menu-item title="Jawaban Refleksi" icon="o-document-text"
                            link="{{ route('laporan.esai') }}" wire:navigate />
                    </x-menu-sub>

                    {{-- ======================================================= --}}
                    {{--                       MENU KHUSUS SISWA                 --}}
                    {{-- ======================================================= --}}
                @elseif(auth()->user()->role === 'Siswa')
                    <x-menu-separator />
                    <x-menu-item title="Daftar Ujian" icon="o-academic-cap" link="{{ route('ujian.list') }}"
                        wire:navigate />
                    <x-menu-item title="Daftar Kuis" icon="o-puzzle-piece" link="{{ route('kuis.list') }}"
                        wire:navigate />

                    <x-menu-sub title="Riwayat & Hasil" icon="o-archive-box">
                        <x-menu-item title="Hasil Ujian" icon="o-chart-bar-square" link="{{ route('ujian.hasil') }}"
                            wire:navigate />
                        <x-menu-item title="Hasil Kuis" icon="o-presentation-chart-line"
                            link="{{ route('kuis.hasil') }}" wire:navigate />
                    </x-menu-sub>
                @endif
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>

</html>
