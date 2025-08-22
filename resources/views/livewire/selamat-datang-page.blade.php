{{-- Ganti seluruh isi file dengan ini --}}

{{-- Latar belakang tetap di div terluar --}}
<div class="relative w-full h-screen bg-cover bg-center"
    style="background-image: url('{{ asset('assets/img/background-petualangan.jpg') }}');">
    {{-- Overlay gelap --}}
    <div class="absolute inset-0 bg-opacity-30"></div>

    {{-- ======================================================= --}}
    {{--     KONTENER BARU DENGAN FLEXBOX & LAYOUT SCROLLABLE    --}}
    {{-- ======================================================= --}}

    {{-- Kontainer ini sekarang mengontrol layout vertikal --}}
    <div class="relative z-10 h-full flex flex-col">

        {{-- HEADER --}}
        <header class="flex justify-between items-center p-6 md:p-8 flex-shrink-0" {{-- flex-shrink-0 penting --}} x-data
            x-init="gsap.from($el, { y: -50, opacity: 0, duration: 1, ease: 'power2.out' })">
            {{-- Logo dengan teks hitam agar terbaca di background terang --}}
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" alt="Logo SIJAKA" class="w-12 h-12">
                <span class="text-2xl font-bold text-gray-800 hidden md:block drop-shadow">SIJAKA</span>
            </div>

            {{-- Tombol Navigasi --}}
            <nav class="flex items-center gap-4">
                @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-button label="Dashboard" icon="o-view-columns"
                            class="btn-ghost text-gray-700 hover:bg-gray-200" />
                    </a>
                @endif
                <livewire:auth.logout />
            </nav>
        </header>

        {{-- KONTEN TENGAH (AREA YANG BISA DI-SCROLL) --}}
        <main class="flex-grow overflow-y-auto flex items-center justify-center p-4">

            <div x-data x-init="gsap.from($el, { y: 50, opacity: 0, duration: 1, ease: 'power2.out', delay: 0.5 })"
                class="bg-white/80 backdrop-blur-md rounded-2xl shadow-2xl max-w-3xl w-full my-auto">
                <div class="p-8 md:p-12 text-gray-800">
                    {{-- Wrapper untuk animasi stagger --}}
                    <div x-data x-init="gsap.from($el.children, { y: 30, opacity: 0, stagger: 0.15, duration: 0.8, ease: 'power2.out', delay: 0.5 })">

                        <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow-md">Halo, Sobat Belajar!</h1>
                        <p class="mt-4 mb-2 max-w-2xl mx-auto text-lg md:text-xl drop-shadow">
                            Kali ini kamu akan mempelajari berbagai hal yang menyenangkan, antara lain:
                        </p>

                        <ol class="space-y-4 mt-3 text-left text-lg">
                            <li class="flex items-center gap-4">
                                <x-icon name="o-eye" class="w-8 h-8 text-secondary flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Mengenal</strong> perbedaan budaya, suku, bahasa, agama, dan kepercayaan di
                                    sekitar kita.
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <x-icon name="o-heart" class="w-8 h-8 text-accent flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Memahami</strong> apa saja masalah yang bisa muncul karena perbedaan itu.
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <x-icon name="o-magnifying-glass-circle"
                                    class="w-8 h-8 text-warning flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Belajar</strong> menghargai perbedaan di sekolah.
                                </div>
                            </li>
                            <li class="flex items-center gap-4">
                                <x-icon name="o-light-bulb" class="w-8 h-8 text-info flex-shrink-0 mt-1" />
                                <div>
                                    <strong>Belajar</strong> menghargai perbedaan di lingkungan masyarakat.
                                </div>
                            </li>
                        </ol>
                        <p class="mt-4 mb-2 max-w-2xl mx-auto text-lg md:text-xl drop-shadow">
                            Dengan mempelajari ini, kamu akan semakin mengerti bahwa perbedaan itu indah dan menjadi
                            kekuatan bagi Bangsa Indonesia.
                        </p>
                        <div class="flex mt-10 justify-center">
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
