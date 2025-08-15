<div class="w-full min-h-screen bg-teal-50 flex items-center justify-center p-4 md:p-8">
    {{-- Tombol Navigasi di Pojok Atas --}}
    <div class="absolute top-6 left-6 md:top-8 md:left-8 z-10">
        @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
            <a href="{{ route('peta-petualangan') }}" wire:navigate>
                <x-button label="Kembali ke Peta" icon="o-arrow-left" class="btn-ghost" />
            </a>
        @endif
    </div>

    {{-- Kontainer Utama --}}
    <div class="w-full max-w-4xl text-center" x-data x-init="gsap.from($el, { scale: 0.9, opacity: 0, duration: 0.7, ease: 'back.out(1.7)' })">
        <x-icon name="o-sparkles" class="w-16 h-16 mx-auto text-teal-500" />
        <h1 class="text-3xl md:text-4xl font-bold text-teal-800 mt-4">{{ $judul }}</h1>
        <p class="text-teal-700 mt-4 mb-8 text-lg">
            Ini adalah kesempatanmu untuk bercerita. Tidak ada jawaban benar atau salah!
        </p>

        <div class="bg-white p-6 rounded-2xl shadow-xl">
            {{-- DAFTAR PERTANYAAN --}}
            <div class="space-y-4">
                @foreach ($daftarPertanyaan as $nomor => $pertanyaan)
                    <div class="bg-white p-6 rounded-xl shadow-md flex items-start gap-4">
                        <div
                            class="flex-shrink-0 bg-green-500 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-xl">
                            {{ $nomor }}
                        </div>
                        <p class="text-gray-700 text-base mt-1 text-start">{{ $pertanyaan }}</p>
                    </div>
                @endforeach
            </div>

            {{-- AREA JAWABAN (Hanya untuk Siswa) --}}
            @if (auth()->user()->role === 'Siswa')
                <div class="mt-10">
                    <x-form wire:submit="tandaiSelesai">
                        <x-textarea wire:model="jawaban" rows="10" placeholder="Tuliskan Jawabanmu disini . . ."
                            class="bg-white text-lg shadow-inner" />

                        <div class="mt-8 text-center">
                            <x-button type="submit" label="Kirim Jawaban & Lanjut ke Pulau Terakhir!"
                                class="btn-success btn-lg rounded-full px-8" icon-right="o-arrow-right"
                                spinner="tandaiSelesai" />
                        </div>
                    </x-form>
                </div>
            @endif
        </div>
    </div>
</div>
