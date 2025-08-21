{{-- Ganti seluruh isi file dengan ini --}}
<div class="relative w-full h-screen overflow-hidden bg-gray-200" {{-- Beri warna fallback --}}>
    {{-- Gambar Latar Belakang Responsif --}}
    <div class="absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-1000"
        style="background-image: url('{{ asset('assets/img/background-petualangan.jpg') }}');" x-data="{ loaded: false }"
        x-init="const img = new Image();
        img.src = '{{ asset('assets/img/background-petualangan.jpg') }}';
        img.onload = () => { loaded = true; }" :class="loaded ? 'opacity-100' : 'opacity-0'"></div>

    {{-- Overlay gradien halus (opsional, tapi mempercantik) --}}
    <div class="absolute inset-0 bg-gradient-to-t from-white/30 to-white/0"></div>

    {{-- KONTEN UTAMA --}}
    <div class="relative z-10 h-full flex flex-col p-6 md:p-12">

        {{-- HEADER dengan Teks Hitam --}}
        <header class="flex justify-between items-center" x-data x-init="gsap.from($el, { y: -50, opacity: 0, duration: 1, ease: 'power2.out' })">
            {{-- Logo --}}
            <div class="flex items-center gap-3 bg-black/10 backdrop-blur-sm p-2 rounded-full">
                <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" alt="Logo SIJAKA" class="w-12 h-12">
                <span class="text-2xl font-bold text-white px-4 hidden md:block">SIJAKA</span>
            </div>

            {{-- Tombol Navigasi dengan Teks Hitam --}}
            <nav class="flex items-center gap-4">
                @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        {{-- Ubah warna teks menjadi hitam/gelap --}}
                        <x-button label="Dashboard" icon="o-view-columns"
                            class="btn-ghost text-gray-700 hover:bg-gray-200" />
                    </a>
                @endif
                {{-- Tombol Logout --}}
                <livewire:auth.logout />
            </nav>
        </header>

        <main class="flex-grow flex flex-col justify-center items-center text-center text-white p-4">
            {{-- 1. Kontainer Luar - Untuk Positioning & Animasi --}}
            <div x-data x-init="gsap.from($el, { y: 50, opacity: 0, duration: 1, ease: 'power2.out', delay: 0.5 })" class="relative" {{-- Posisi relative sebagai jangkar --}}>
                {{-- 2. Kontainer Background Blur - Layer Bawah --}}
                <div
                    class="absolute inset-0 bg-black/20 backdrop-blur-md rounded-3xl transform transition-all duration-500">
                </div>
                {{-- 3. Kontainer Konten - Layer Atas --}}
                <div class="relative z-10 p-8 md:p-12">
                    {{-- Wrapper untuk animasi stagger --}}
                    <div x-data x-init="gsap.from($el.children, { y: 30, opacity: 0, stagger: 0.15, duration: 0.8, ease: 'power2.out', delay: 0.5 })">
                        <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow-md">Capaian Pembelajaran</h1>
                        <p class="mt-4 mb-2 max-w-2xl mx-auto text-lg md:text-xl drop-shadow">
                            Siswa membedakan dan menghargai identitas diri, keluarga, dan teman-temannya sesuai budaya,
                            suku bangsa, bahasa, agama, dan kepercayaannya di lingkungan rumah, sekolah, dan masyarakat.
                        </p>
                        <h1 class="text-2xl md:text-3xl font-extrabold drop-shadow-md">Tujuan Pembelajaran</h1>
                        <ol class="space-y-4 text-left text-lg">
                            <li class="flex items-center gap-4">
                                <x-icon name="o-eye" class="w-8 h-8 text-secondary flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Mengidentifikasi</strong> berbagai bentuk keberagaman di sekitarmu, seperti
                                    suku, budaya, bahasa, dan agama.
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <x-icon name="o-heart" class="w-8 h-8 text-accent flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Menghargai</strong> berbagai bentuk keberagaman yang ada di lingkungan
                                    sekolah.
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <x-icon name="o-magnifying-glass-circle"
                                    class="w-8 h-8 text-warning flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Menganalisis</strong> tantangan atau masalah yang bisa muncul karena adanya
                                    perbedaan.
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <x-icon name="o-light-bulb" class="w-8 h-8 text-info flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Menemukan solusi</strong> untuk mengatasi masalah keberagaman bersama
                                    teman-teman.
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <x-icon name="o-heart" class="w-8 h-8 text-red-600 flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Menghargai</strong> berbagai bentuk keberagaman sesuai budaya, suku bangsa,
                                    bahasa, agama dan kepercayaannya di lingkungan sekolah.
                                </div>
                            </li>
                            <li class="flex items-start gap-4">
                                <x-icons.handshake class="w-8 h-8 text-accent flex-shrink-0 mt-1" />

                                <div>
                                    <strong>Menghargai</strong> berbagai bentuk keberagaman sesuai budaya, suku bangsa,
                                    bahasa, agama dan kepercayaannya di lingkungan masyarakat.
                                </div>
                            </li>
                        </ol>
                        <div class="mt-10">
                            <a href="{{ route('peta-petualangan') }}" wire:navigate
                                class="btn btn-primary btn-lg rounded-full px-10 transform hover:scale-105 transition-transform shadow-lg">
                                Mari Berpetualang!
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
