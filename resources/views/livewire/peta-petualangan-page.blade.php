<div class="relative w-full h-screen overflow-hidden bg-gray-200" {{-- Beri warna fallback --}}>
    {{-- Gambar Latar Belakang Responsif --}}
    <div class="absolute inset-0 w-full h-full bg-cover bg-center transition-opacity duration-1000"
        style="background-image: url('{{ asset('assets/img/background-peta.jpg') }}');" x-data="{ loaded: false }"
        x-init="const img = new Image();
        img.src = '{{ asset('assets/img/background-peta.jpg') }}';
        img.onload = () => { loaded = true; }" :class="loaded ? 'opacity-100' : 'opacity-0'"></div>

    {{-- Overlay gradien halus (opsional, tapi mempercantik) --}}
    <div class="absolute inset-0 bg-gradient-to-t from-white/30 to-white/0"></div>

    <div x-data="floatingAnimations()" x-init="$nextTick(() => init())" class="absolute inset-0 pointer-events-none">
        {{-- AWAN BELAKANG (z-10) --}}
        <div class="absolute inset-0 z-10">
            <template x-for="i in 5" :key="'back-cloud-' + i">
                <img :src="`{{ asset('assets/img/effects/awan') }}${ (i % 3) + 1 }.png`" alt="Awan Belakang"
                    class="floating-element absolute">
            </template>
        </div>

        {{-- PESAWAT (z-30) - DI ATAS PETA --}}
        <div class="absolute inset-0 z-30">
            <template x-for="i in 3" :key="'plane-' + i">
                <img src="{{ asset('assets/img/effects/pesawat.png') }}" alt="Pesawat Kertas"
                    class="plane-element absolute w-20 md:w-24 opacity-0">
            </template>
        </div>

        {{-- AWAN DEPAN (z-40) --}}
        <div class="absolute inset-0 z-40">
            <template x-for="i in 3" :key="'front-cloud-' + i">
                <img :src="`{{ asset('assets/img/effects/awan') }}${ (i % 3) + 1 }.png`" alt="Awan Depan"
                    class="floating-element absolute">
            </template>
        </div>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="relative z-10 overflow-scroll h-full flex flex-col p-6 md:p-12">

        {{-- HEADER --}}
        <header class="flex justify-between items-center" x-data x-init="gsap.from($el, { y: -50, opacity: 0, duration: 1, ease: 'power2.out' })">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" alt="Logo SIJAKA" class="w-12 h-12">
                {{-- Ubah warna teks menjadi hitam/gelap --}}
                <span class="text-2xl font-bold text-gray-800 hidden md:block drop-shadow">SIJAKA</span>
            </div>

            {{-- Tombol Navigasi dengan Teks Hitam --}}
            <nav class="flex items-center gap-4">
                {{-- Tombol Kembali ke Halaman Sebelumnya --}}
                <a href="{{ route('selamat-datang') }}" wire:navigate>
                    <x-button label="Kembali" icon="o-arrow-left"
                        class="btn-ghost text-white bg-black hover:bg-white hover:text-black" />
                </a>
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

        {{-- KONTEN TENGAH: PETA --}}
        <main class="flex-grow flex flex-col justify-center items-center text-center text-white">
            <div x-data="{
                // Fungsi animasi hover
                onIslandHover(element) {
                        const svg = element.querySelector('.island-svg');
                        gsap.to(svg, { scale: 1.1, duration: 0.3, ease: 'power2.out' });
                        gsap.to(svg, { filter: 'drop-shadow(0 0 15px rgba(255, 204, 0, 0.9))', duration: 0.3 });
                    },
                    // Fungsi untuk mengembalikan ke state normal
                    onIslandLeave(element) {
                        const svg = element.querySelector('.island-svg');
                        gsap.to(svg, { scale: 1, duration: 0.3, ease: 'power2.out' });
                        gsap.to(svg, { filter: 'none', duration: 0.3 });
                    },
                    // Fungsi untuk animasi klik
                    onIslandClick(element, islandName, url) { // <-- 1. Tambahkan parameter 'url'
                        const svg = element.querySelector('.island-svg');
                        const tl = gsap.timeline({
                            // 2. Tambahkan onComplete untuk redirect setelah animasi
                            onComplete: () => {
                                Livewire.navigate(url);
                            }
                        });
            
                        tl.to(svg, { scale: 0.9, duration: 0.1, ease: 'power2.inOut' })
                            .to(svg, { scale: 1, duration: 0.5, ease: 'elastic.out(1, 0.5)' });
                    }
            }" x-init="// Atur state awal semua elemen agar tidak 'flash'
            gsap.set($refs.islands.children, { opacity: 0 });
            gsap.set('.sign-board', { opacity: 0, y: -20 });
            gsap.set('.sign-pin', { opacity: 0, y: -20 });
            
            if (typeof SplitType === 'undefined') {
                console.error('SplitType from CDN is not loaded.');
                return;
            }
            
            // Inisialisasi SplitType (ini sekarang akan berhasil)
            gsap.set('.title-line-1, .title-line-2', { opacity: 1 }); // Hapus opacity: 0 dari style inline
            const line1 = new SplitType('.title-line-1', { types: 'chars' });
            
            const tl = gsap.timeline({ delay: 0.5 });
            
            tl.from(line1.chars, {
                y: -50,
                opacity: 0,
                scale: 1.5,
                rotationZ: () => gsap.utils.random(-40, 40),
                ease: 'back.out(2)',
                stagger: 0.05,
                duration: 0.8
            });
            
            tl.from('.title-line-2', {
                y: 50,
                opacity: 0,
                duration: 1.0,
                ease: 'power3.out'
            }, '-=0.6');
            
            // 1. Animasikan semua kontainer pulau muncul
            tl.to($refs.islands.children, {
                opacity: 1,
                stagger: 0.2,
                duration: 0.5,
                onComplete: () => {
                    // 2. Setelah pulau muncul, animasikan pin dan papan nama
                    const signs = gsap.utils.toArray('.sign-group');
            
                    // Animasikan semua pin 'jatuh' secara bersamaan
                    gsap.to('.sign-pin', {
                        opacity: 1,
                        y: 0,
                        duration: 1,
                        ease: 'bounce.out',
                        stagger: 0.1, // Stagger kecil agar tidak terlalu serempak
                        onComplete: () => {
                            // 3. Setelah pin mendarat, mulai animasi bouncing tanpa henti
                            gsap.to('.sign-pin', {
                                y: -8, // Seberapa tinggi pantulannya
                                duration: 1.5,
                                ease: 'sine.inOut',
                                repeat: -1, // Ulangi selamanya
                                yoyo: true, // Bolak-balik (naik-turun)
                            });
                        }
                    });
            
                    // Animasikan papan nama muncul (fade in) setelah pin
                    gsap.to('.sign-board', {
                        opacity: 1,
                        y: 0,
                        duration: 0.5,
                        ease: 'power2.out',
                        stagger: 0.1,
                        delay: 0.3 // Muncul sedikit setelah pin mulai jatuh
                    });
                }
            });" class="w-full max-w-xl lg:max-w-4xl mx-auto">
                <h1 class="font-lilita text-5xl md:text-7xl text-black text-center mb-8"
                    style="
                        -webkit-text-stroke: 2px #1E3A8A;
                        text-stroke: 2px #1E3A8A;
                        paint-order: stroke fill;
                    ">
                    {{-- Baris pertama, beri x-ref --}}
                    <span class="title-line-1 block"
                        style="
                            font-family: 'Lilita One', cursive;
                            font-size: 4.5rem; /* setara dengan text-7xl */
                            color: black;
                            text-align: center;
                            margin-bottom: 2rem;
                            -webkit-text-stroke: 2px #1E3A8A;
                            text-stroke: 2px #1E3A8A;
                            paint-order: stroke fill;
                            opacity: 0; /* Untuk animasi */
                        ">PETA
                        INDONESIA</span>

                    {{-- Baris kedua, beri x-ref dan style berbeda --}}
                    <span class="title-line-2 block text-3xl md:text-5xl text-gray-700"
                        style="-webkit-text-stroke: 1px #92400E; text-stroke: 1px #92400E;">
                        Silahkan Pilih Pulau untuk Memulai Pembelajaran!
                    </span>
                </h1>

                {{-- ============================================= --}}
                {{--        KONTAINER PETA DENGAN LAYER            --}}
                {{-- ============================================= --}}
                <div class="relative w-full">
                    {{-- Layer 1: Peta Dasar (Latar Belakang) --}}
                    <img x-ref="mapBase" src="{{ asset('assets/img/peta-indonesia.svg') }}" alt="Peta Dasar Indonesia"
                        class="w-full h-auto" style="opacity: 0;">

                    {{-- Layer 2: Pulau-Pulau Interaktif (SVG) --}}
                    <div x-ref="islands">
                        {{-- Lakukan perulangan pada data pulau dari komponen --}}
                        @foreach ($this->pulauData() as $pulau)
                            @php
                                $isClickable = in_array($pulau['status'], ['aktif', 'terbuka']);
                            @endphp

                            <div class="island-container absolute {{ $pulau['lebar'] }}" style="{{ $pulau['posisi'] }}"
                                @if ($isClickable) @@mouseover="onIslandHover($el)"
                                    @@mouseleave="onIslandLeave($el)"
                                    @@click="onIslandClick($el, '{{ $pulau['nama'] }}', '{{ $this->getLinkForPulau($pulau['id']) }}')" @endif>
                                <img src="{{ asset('assets/img/islands/' . $pulau['id'] . '.svg') }}"
                                    alt="Pulau {{ $pulau['nama'] }}" @class([
                                        'island-svg w-full transition-all duration-300',
                                        'cursor-pointer' => $isClickable,
                                        'opacity-40 grayscale cursor-not-allowed' =>
                                            $pulau['status'] === 'terkunci',
                                        'opacity-60 grayscale' => $pulau['status'] === 'selesai',
                                    ])>

                                <div @class([
                                    'sign-group absolute -translate-x-1/2 flex flex-col items-center pointer-events-none',
                                    'flex' => $pulau['status'] !== 'terkunci',
                                    'hidden' => $pulau['status'] === 'terkunci',
                                ]) style="{{ $pulau['posisipin'] }}">
                                    {{-- Tampilkan centang jika sudah selesai atau terbuka --}}
                                    @if ($pulau['status'] === 'selesai' || $pulau['status'] === 'terbuka')
                                        <x-icon name="o-check-badge"
                                            class="w-10 h-10 text-success absolute -top-3 -right-10 bg-white rounded-full p-1 shadow-lg" />
                                    @endif

                                    <x-icon name="s-map-pin"
                                        class="sign-pin w-10 h-10 text-{{ $pulau['warna'] }} drop-shadow-lg" />
                                    <div
                                        class="sign-board bg-{{ $pulau['warna'] }} text-white font-bold text-sm text-center px-3 py-1 rounded-md shadow-lg z-10">
                                        {{ $pulau['nama'] }}
                                    </div>

                                    @if ($pulau['status'] === 'aktif')
                                        <span
                                            class="absolute top-0 left-0 w-full h-full bg-{{ $pulau['warna'] }} rounded-full animate-ping opacity-50"></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- ============================================= --}}
            </div>
        </main>
    </div>
</div>
