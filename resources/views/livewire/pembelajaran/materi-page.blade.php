{{-- Ganti seluruh isi file materi-page.blade.php --}}
<div class="w-full min-h-screen bg-gray-100">
    {{-- HEADER HALAMAN --}}
    <header class="bg-white shadow p-4 flex justify-between items-center sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <x-icon name="o-book-open" class="w-8 h-8 text-orange-600" />
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">{{ $judul }}</h1>
        </div>
        <div>
            @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                <a href="{{ route('peta-petualangan') }}" wire:navigate>
                    <x-button label="Kembali ke Peta" icon="o-arrow-left" class="btn-ghost" />
                </a>
            @endif
        </div>
    </header>

    {{-- KONTENER UTAMA UNTUK EMBED CANVA --}}
    <main class="max-w-7xl mx-auto py-8 px-4 md:px-8">
        <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-xl">

            {{-- Kontainer Responsif untuk Iframe (16:9 Aspect Ratio) --}}
            <div class="relative w-full h-0 pb-[56.25%] rounded-lg overflow-hidden shadow-inner">
                <iframe loading="lazy" class="absolute top-0 left-0 w-full h-full"
                    src="https://www.canva.com/design/DAGsfMw_gck/rommOYyGL4degOnD5OsYxg/view?embed"
                    allowfullscreen="allowfullscreen" allow="fullscreen">
                </iframe>
            </div>
        </div>

        {{-- TOMBOL LANJUT (Hanya untuk Siswa) --}}
        @if (auth()->user()->role === 'Siswa')
            <div class="mt-10 mb-10 text-center" {{-- 1. Inisialisasi Alpine.js --}} x-data="{
                sisaWaktu: 150, // Waktu dalam detik
                timerSelesai: false,
                init() {
                    const timer = setInterval(() => {
                        this.sisaWaktu--;
                        if (this.sisaWaktu <= 0) {
                            this.timerSelesai = true;
                            clearInterval(timer);
                        }
                    }, 1000);
                },
                formatWaktu() {
                    if (this.sisaWaktu <= 0) return '00:00';
                    let minutes = Math.floor(this.sisaWaktu / 60);
                    let seconds = this.sisaWaktu % 60;
                    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                }
            }" x-init="init()">
                {{-- 2. Tampilkan Tombol Selesai jika timer sudah habis --}}
                <div x-show="timerSelesai" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <x-button label="Hebat! Lanjut ke Pulau Berikutnya" wire:click="tandaiSelesai"
                        class="btn-primary btn-lg rounded-full px-8 animate-bounce" icon-right="o-arrow-right"
                        spinner="tandaiSelesai" />
                </div>

                {{-- 3. Tampilkan Tombol Disabled dengan hitungan mundur jika timer masih berjalan --}}
                <div x-show="!timerSelesai" x-transition>
                    <x-button class="btn-disabled btn-lg rounded-full px-8" icon-right="o-clock">
                        {{-- Tampilkan hitungan mundur di dalam tombol --}}
                        <span>Lanjut dalam</span>
                        <span x-text="formatWaktu()" class="font-mono ml-2"></span>
                    </x-button>
                    <p class="text-sm text-gray-500 mt-2">Luangkan waktumu untuk memahami materi ini ya!</p>
                </div>
            </div>
        @endif
    </main>
</div>
