<div class="w-full h-screen">
    {{-- TAHAP 1: Halaman Mulai --}}
    @if ($tahap === 'mulai')
        <div wire:key="tahap-mulai" class="w-full h-full bg-indigo-100 flex items-center justify-center p-8">
            <div class="text-center max-w-2xl" x-data x-init="gsap.from($el, { y: 50, opacity: 0, duration: 1, ease: 'back.out(1.7)' })">
                <x-icon name="o-trophy" class="w-20 h-20 mx-auto text-indigo-500" />
                <h1 class="text-4xl font-bold text-indigo-800 mt-4">Penilaian Akhir: Petualangan Papua!</h1>
                <p class="text-indigo-700 mt-4 text-lg">
                    Ini adalah ujian terakhir! Kamu akan mengerjakan soal <strong>Pilihan Ganda</strong>, lalu
                    dilanjutkan dengan <strong>Kuis Menjodohkan</strong>.
                </p>
                <div class="mt-10">
                    {{-- Tombol akan ditampilkan secara kondisional --}}
                    @if ($ujianPilgan && $kuisMenjodohkan)
                        <x-button label="Mulai Penilaian!" class="btn-primary btn-lg" wire:click="mulaiPenilaian"
                            spinner="mulaiPenilaian" />
                    @else
                        <x-button label="Penilaian belum siap" class="btn-disabled btn-lg" />
                        <p class="text-sm text-gray-500 mt-2">Pastikan Ujian dan Kuis sudah dibuat dan di-"Publish".</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- TAHAP 2: Pengerjaan Ujian Pilihan Ganda --}}
    @if ($tahap === 'pilgan' && $ujianPilgan)
        <livewire:ujian.pengerjaan :ujian="$ujianPilgan" :parent-runner-id="$this->getId()" wire:key="'pilgan-runner-' . $ujianPilgan->id" />
    @endif

    {{-- TAHAP 3: Pengerjaan Kuis Menjodohkan --}}
    @if ($tahap === 'menjodohkan' && $kuisMenjodohkan)
        <div wire:key="tahap-menjodohkan">
            <livewire:kuis-menjodohkan.pengerjaan :kuis-menjodohkan="$kuisMenjodohkan" :histori-ujian-id="$historiUjianId" :parent-runner-id="$this->getId()" />
        </div>
    @endif

    {{-- TAHAP 4: Selesai (Menampilkan loading sebelum redirect oleh SweetAlert) --}}
    @if ($tahap === 'selesai')
        <div wire:key="tahap-selesai" class="w-full h-full bg-gray-100 flex flex-col items-center justify-center">
            <x-loading class="loading-lg text-primary" /> {{-- <== INI YANG BENAR --}}
            <p class="mt-4 text-gray-600">Menyimpan hasil akhir...</p>
        </div>
    @endif
</div>
