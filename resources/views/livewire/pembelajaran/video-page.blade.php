<div class="w-full h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-4 md:p-8 relative"
    x-data="{
        videoSelesai: false,
        showModal: @entangle('sumateraSelesaiModal')
    }">

    {{-- Tombol Navigasi di Pojok Atas --}}
    <div class="absolute top-6 left-6 md:top-8 md:left-8 z-10">
        {{-- Tombol Kembali hanya untuk Admin/Guru --}}
        @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
            <a href="{{ route('peta-petualangan') }}" wire:navigate>
                <x-button label="Kembali ke Peta" icon="o-arrow-left" class="btn-ghost text-white hover:bg-white/20" />
            </a>
        @endif
    </div>

    {{-- Konten Utama dengan Animasi Masuk --}}
    <div class="text-center w-full overflow-scroll" x-data x-init="gsap.from($el, { y: 30, opacity: 0, duration: 0.8, ease: 'power2.out' })">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $judul }}</h1>
        <p class="text-gray-400 mb-8">Tonton video ini sampai selesai untuk membuka pulau berikutnya!</p>

        {{-- Kontainer Video --}}
        <div class="w-full max-w-4xl mx-auto aspect-video bg-black rounded-xl shadow-2xl shadow-primary/20">
            <video class="w-full h-full rounded-xl" controls preload="metadata"
                @@ended="
                    videoSelesai = true;
                    if ('{{ $pulau }}' === 'sumatera') {
                        showModal = true;
                    }
                ">
                <source src="{{ asset('videos/' . $videoFile) }}" type="video/mp4">
                Browser Anda tidak mendukung tag video.
            </video>
        </div>
        @if ($pulau == 'sumatera')
            <p class="text-gray-400 mt-4">Sumber pada video ini dari <a
                    href="https://www.youtube.com/watch?v=cbD_yqfYx9g&t=4s" target="_blank">Youtube KEMENBUD</a></p>
        @endif

        {{-- Tombol Lanjut (Hanya untuk Siswa) --}}
        <div class="mt-8 h-16">
            @if (auth()->user()->role === 'Siswa' && $pulau !== 'sumatera')
                <div x-show="videoSelesai" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <x-button label="Lanjut ke Pulau {{ Str::ucfirst($pulauBerikutnya) }}" wire:click="tandaiSelesai"
                        class="btn-primary btn-lg animate-pulse rounded-full px-8" icon-right="o-arrow-right"
                        spinner="tandaiSelesai" />
                </div>
                <div x-show="!videoSelesai" x-transition>
                    <div class="flex items-center justify-center gap-2 text-gray-400">
                        <x-icon name="o-information-circle" class="w-6 h-6" />
                        <span>Selesaikan video untuk melanjutkan</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($pulau === 'sumatera')
        <x-modal wire:model="sumateraSelesaiModal">
            <x-slot:header>
                <h2 class="text-2xl font-bold text-gray-600">
                    Yuk, Pikirkan Sejenak!
                </h2>
            </x-slot:header>
            <div class="text-left">
                <p class="mb-6 text-gray-600">
                    Hebat, kamu sudah selesai menonton video pertama! Sebelum lanjut, coba jawab pertanyaan-pertanyaan
                    ini.
                </p>

                {{-- Daftar Pertanyaan Refleksi --}}
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 bg-blue-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">
                            1</div>
                        <p class="text-gray-700 mt-1">Apa yang kamu pelajari dari video yang kamu tonton?</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 bg-teal-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">
                            2</div>
                        <p class="text-gray-700 mt-1">Tuliskan budaya yang terlihat di video yang kamu sukai! Mengapa?
                        </p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 bg-amber-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">
                            3</div>
                        <p class="text-gray-700 mt-1">Bagaimana cara kita bisa menjaga keberagaman budaya seperti yang
                            ada di video?</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div
                            class="flex-shrink-0 bg-red-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">
                            4</div>
                        <p class="text-gray-700 mt-1">Apakah kamu ingin belajar lebih banyak tentang budaya yang ada di
                            Indonesia? Jelaskan alasannya?</p>
                    </div>
                </div>
            </div>

            <x-slot:actions>
                {{-- Tombol Lanjut sekarang ada di sini --}}
                <x-button label="Lanjut ke Pulau Jawa" class="btn-primary w-full" wire:click="tandaiSelesai"
                    spinner="tandaiSelesai" />
            </x-slot:actions>
        </x-modal>
    @endif
</div>
