<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
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
            <x-app-brand class="px-5 pt-4" />

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
                                <livewire:auth.logout class="w-full" />
                            </x-dropdown>
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Beranda" icon="o-home" link="/dashboard" />
                @if (auth()->user()->role === 'Admin')
                    <x-menu-sub title="Master Data" icon="o-circle-stack">
                        <x-menu-item title="Sekolah" icon="o-building-office-2" link="{{ route('sekolah.index') }}" />
                        <x-menu-item title="Kelas" icon="o-table-cells" link="{{ route('kelas.index') }}" />
                        <x-menu-item title="Manajemen User" icon="o-users" link="{{ route('users.index') }}" />
                    </x-menu-sub>
                @endif
                @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                    <x-menu-item title="Siswa per Kelas" icon="o-identification" link="{{ route('siswa.manage') }}" />
                    <x-menu-item title="Manajemen Ujian" icon="o-academic-cap" link="{{ route('ujian.index') }}" />
                    <x-menu-item title="Manajemen Kuis" icon="o-arrows-right-left" link="{{ route('kuis.index') }}" />
                @endif
                @if (auth()->user()->role === 'Siswa')
                    <x-menu-item title="Daftar Ujian" icon="o-academic-cap" link="{{ route('ujian.list') }}" />
                    <x-menu-item title="Daftar Kuis" icon="o-puzzle-piece" link="{{ route('kuis.list') }}" />
                @endif
                <x-menu-item title="Hasil Ujian" icon="o-chart-bar-square" link="{{ route('ujian.hasil') }}" />
                <x-menu-item title="Hasil Kuis" icon="o-presentation-chart-line" link="{{ route('kuis.hasil') }}" />
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
