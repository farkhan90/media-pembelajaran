@php
    // Definisikan jumlah slide Anda di satu tempat
    $jumlahSlide = 5;
@endphp
<div x-data="{ activeSlide: 1, totalSlides: {{ $jumlahSlide }}, mobileMenuOpen: false }" x-init="setInterval(() => { activeSlide = (activeSlide % totalSlides) + 1 }, 5000)" class="relative w-full h-screen overflow-hidden bg-gray-800">
    {{-- CAROUSEL LATAR BELAKANG --}}
    <div class="absolute inset-0 w-full h-full">
        {{-- Gunakan variabel PHP di sini juga untuk konsistensi --}}
        <div class="absolute inset-0 z-0"> {{-- z-0 untuk lapisan paling bawah --}}
            @for ($i = 1; $i <= $jumlahSlide; $i++)
                <img x-show="activeSlide === {{ $i }}"
                    x-transition:enter="transition-opacity ease-in-out duration-1000" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in-out duration-1000"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    src="{{ asset('assets/img/carousel/slide' . $i . '.jpg') }}"
                    alt="Latar Belakang SIJAKA {{ $i }}" class="absolute w-full h-full object-cover"
                    style="display: none;">
            @endfor
        </div>
    </div>

    {{-- KONTEN UTAMA (HEADER DAN JUDUL) --}}
    <div class="relative z-10 h-full flex flex-col p-6 md:p-12">

        {{-- HEADER --}}
        <header x-init="gsap.from($el, { y: -50, opacity: 0, duration: 1, ease: 'power2.out' })" class="flex justify-between items-center">
            <div class="flex items-center gap-3 drop-shadow-lg">
                <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" alt="Logo SIJAKA" class="w-12 h-12">
                <span class="text-2xl font-bold text-white hidden md:block">SIJAKA</span>
            </div>

            {{-- Tombol Navigasi --}}
            <nav>
                {{-- NAVIGASI DESKTOP --}}
                <div class="hidden md:flex items-center gap-2 bg-black/10 backdrop-blur-sm p-2 rounded-full">
                    <x-button label="Info SIJAKA" wire:click="$toggle('infoModal')"
                        class="btn-ghost text-white hover:bg-white/20 rounded-full" />
                    <x-button label="Petunjuk" wire:click="$toggle('petunjukModal')"
                        class="btn-ghost text-white hover:bg-white/20 rounded-full" />
                    <x-button label="Pengembang" wire:click="$toggle('pengembangModal')"
                        class="btn-ghost text-white hover:bg-white/20 rounded-full" />
                </div>

                {{-- NAVIGASI MOBILE (dikontrol oleh div utama di bawah) --}}
                <div class="md:hidden">
                    <div @@click="$dispatch('toggle-menu')" class="cursor-pointer">
                        <x-button icon="o-bars-3"
                            class="btn-ghost text-white hover:bg-white/20 btn-circle pointer-events-none" />
                    </div>
                </div>
            </nav>
        </header>

        {{-- KONTEN TENGAH --}}
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
                        {{-- Judul Selamat Datang --}}
                        <h1 class="text-3xl md:text-5xl font-bold tracking-wide drop-shadow-lg">
                            SELAMAT DATANG!
                        </h1>
                        {{-- Nama Brand "SIJAKA" --}}
                        <h2
                            class="text-5xl md:text-7xl font-extrabold my-4 text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-amber-500 drop-shadow-xl">
                            “SIJAKA”
                        </h2>
                        {{-- Kepanjangan --}}
                        <h3 class="text-xl md:text-2xl font-semibold italic text-gray-200 drop-shadow-lg">
                            (Sistem Interaktif Jelajah Keberagaman Indonesia)
                        </h3>
                        {{-- Deskripsi --}}
                        <p
                            class="mt-6 max-w-3xl mx-auto text-md md:text-lg text-gray-200 drop-shadow-md leading-relaxed">
                            SIJAKA adalah situs khusus untuk mempelajari Pendidikan Pancasila materi <strong>Keberagaman
                                di Lingkungan Sekitar</strong>.
                            <br class="hidden md:block">
                            Situs ini menyediakan materi, video, dan kuis interaktif yang seru!
                        </p>
                        {{-- Tombol Aksi --}}
                        <div class="mt-10">
                            <a href="{{ route('login') }}"
                                class="btn btn-primary btn-lg rounded-full px-10 transform hover:scale-105 transition-transform shadow-lg border-2 border-white/50">
                                <x-icon name="o-arrow-right-on-rectangle" class="w-6 h-6 mr-2" />
                                <span class="font-semibold text-sm md:text-lg">Masuk & Mulai Belajar</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div x-data="{ open: false }" @@toggle-menu.window="open = !open"
        @@click.outside="open = false" x-show="open"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" x-cloak
        class="absolute top-20 right-6 w-64 bg-white/80 backdrop-blur-lg rounded-xl shadow-2xl text-gray-800 z-50 overflow-hidden"
        style="display: none;" x-init="$watch('open', value => {
            if (value) {
                gsap.from($refs.menuItems.children, {
                    opacity: 0,
                    x: -20,
                    stagger: 0.05,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            }
        })">
        <div class="p-2" x-ref="menuItems">
            <a href="#" wire:click.prevent="$toggle('infoModal'); open = false"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-primary hover:text-white transition-colors">
                <x-icon name="o-information-circle" class="w-6 h-6" />
                <span class="font-semibold">Info SIJAKA</span>
            </a>
            <a href="#" wire:click.prevent="$toggle('petunjukModal'); open = false"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-primary hover:text-white transition-colors">
                <x-icon name="o-question-mark-circle" class="w-6 h-6" />
                <span class="font-semibold">Petunjuk</span>
            </a>
            <hr class="my-1 border-gray-500">
            <a href="#" wire:click.prevent="$toggle('pengembangModal'); open = false"
                class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-primary hover:text-white transition-colors">
                <x-icon name="o-user-circle" class="w-6 h-6" />
                <span class="font-semibold">Pengembang</span>
            </a>
        </div>
    </div>

    {{-- ======================================================= --}}
    {{--                      AREA MODAL                         --}}
    {{-- ======================================================= --}}

    {{-- Modal Info SIJAKA --}}
    <x-modal wire:model="infoModal" title="Tentang SIJAKA">
        <div class="prose max-w-none">
            <p><strong>SIJAKA (Sistem Interaktif Jelajah Keberagaman Indonesia)</strong> adalah sebuah platform media
                penunjang
                pembelajaran
                berbasis web yang dirancang khusus untuk siswa sekolah dasar. Tujuan utama SIJAKA adalah membuat proses
                belajar menjadi lebih interaktif, menyenangkan, dan efektif.</p>
            <h3 class="mt-6">Karakteristik SIJAKA</h3>
            <ul>
                <li><strong>Interaktif:</strong> Dilengkapi dengan kuis pilihan ganda dan menjodohkan yang menantang.
                </li>
                <li><strong>Adaptif:</strong> Tampilan dan materi disesuaikan untuk Admin, Guru, dan Siswa.</li>
                <li><strong>Visual & Menarik:</strong> Didesain dengan warna-warna ceria dan animasi untuk meningkatkan
                    motivasi belajar.</li>
                <li><strong>Terstruktur:</strong> Materi dan penilaian dikelola per kelas, memudahkan guru dalam
                    memantau perkembangan siswa.</li>
            </ul>
        </div>
        <x-slot:actions>
            <x-button label="Mengerti!" @click="$wire.infoModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>

    {{-- Modal Petunjuk --}}
    <x-modal wire:model="petunjukModal" title="Panduan Petualang Cerdas!">

        <div class="space-y-6">
            {{-- Langkah 1: Masuk --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-icon name="o-key" class="w-10 h-10 text-primary" />
                </div>
                <div>
                    <h3 class="font-bold text-lg">Langkah 1: Buka Gerbang Petualangan</h3>
                    <p class="text-gray-600">
                        Klik tombol <strong>"Masuk & Mulai Belajar"</strong>, lalu masukkan <strong>Nama
                            Pengguna</strong> dan <strong>Kata Sandi</strong> rahasiamu yang diberikan oleh Bapak/Ibu
                        Guru.
                    </p>
                </div>
            </div>

            {{-- Langkah 2: Peta Ajaib --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-icon name="o-map" class="w-10 h-10 text-success" />
                </div>
                <div>
                    <h3 class="font-bold text-lg">Langkah 2: Jelajahi Peta Petualangan</h3>
                    <p class="text-gray-600">
                        Setelah masuk, kamu akan melihat sebuah peta ajaib! Pulau yang <strong>berwarna dan
                            berdenyut</strong> adalah tujuanmu selanjutnya. Klik pulau itu untuk memulai pembelajaran.
                    </p>
                </div>
            </div>

            {{-- Langkah 3: Taklukkan Pulau --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-icon name="o-rocket-launch" class="w-10 h-10 text-accent" />
                </div>
                <div>
                    <h3 class="font-bold text-lg">Langkah 3: Selesaikan Setiap Tantangan</h3>
                    <p class="text-gray-600">
                        Setiap pulau memiliki tantangan seru: menonton <strong>video</strong>, membaca
                        <strong>materi</strong>, menulis <strong>refleksi</strong>, atau mengerjakan
                        <strong>kuis</strong>! Selesaikan tantangannya untuk membuka pulau berikutnya.
                    </p>
                </div>
            </div>

            {{-- Langkah 4: Menjadi Juara --}}
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <x-icon name="o-trophy" class="w-10 h-10 text-warning" />
                </div>
                <div>
                    <h3 class="font-bold text-lg">Langkah 4: Jadilah Juara Keberagaman!</h3>
                    <p class="text-gray-600">
                        Setelah menyelesaikan semua pulau, kamu akan menjadi ahli dalam menghargai perbedaan. Kamu juga
                        bisa mengunjungi kembali semua pulau untuk belajar lagi kapan pun kamu mau!
                    </p>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Aku Mengerti, Ayo Mulai!" @click="$wire.petunjukModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>

    {{-- Modal Pengembang --}}
    <x-modal wire:model="pengembangModal" title="Profil Pengembang">
        <div class="flex flex-col items-center text-center">
            <x-avatar :image="asset('assets/img/foto-profil.jpg')" class="!w-32 !h-32 mb-4 ring-4 ring-primary ring-offset-2" />
            <h3 class="text-2xl font-bold">Lia Pratiwi</h3>
            <p class="text-gray-500">Mahasiswa Program Studi Magister Pendidikan Dasar</p>
            <p class="text-gray-500">Universitas Negeri Yogyakarta</p>
            <hr class="my-4 w-1/2">
            <p class="max-w-md">Web aplikasi SIJAKA ini dikembangkan sebagai bagian dari penelitian untuk menyelesaikan
                tesis. Diharapkan aplikasi ini dapat menjadi media penunjang pembelajaran yang bermanfaat bagi siswa.
            </p>
        </div>
        <x-slot:actions>
            <x-button label="Keren!" @click="$wire.pengembangModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
