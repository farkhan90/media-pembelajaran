<div>
    <x-header title="Daftar Kuis Menjodohkan" subtitle="Pilih kuis yang ingin kamu kerjakan." />

    <h2 class="text-xl font-bold mt-6 mb-3">Kuis Menjodohkan</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($ujians as $kuis)
            @php
                // Cek apakah ada histori untuk kuis ini di collection hasil agregat kita
                $histori = $historiKuisSiswa->get($kuis->id);
            @endphp

            <x-card :title="$kuis->judul" :subtitle="$kuis->deskripsi" shadow>
                <div class="mb-4">
                    {{-- Tampilkan skor tertinggi dan jumlah percobaan dari hasil agregat --}}
                    @if ($histori)
                        <div class="text-sm">Skor Terbaik: <span
                                class="font-bold text-success">{{ round($histori->skor_tertinggi, 2) }}</span>
                        </div>
                        <div class="text-xs text-gray-500">Percobaan: {{ $histori->jumlah_percobaan }}x</div>
                    @else
                        <div class="text-sm text-gray-500">Belum pernah dikerjakan.</div>
                    @endif
                </div>

                <x-slot:actions>
                    {{-- Logika untuk tombol dinamis (tetap sama, tapi sekarang lebih akurat) --}}
                    @if ($histori)
                        <x-button label="Kerjakan Lagi" icon="o-arrow-path" class="btn-secondary"
                            link="{{ route('kuis.kerjakan', $kuis->id) }}" wire:navigate />
                    @else
                        <x-button label="Mulai Kerjakan" icon="o-play" class="btn-primary"
                            link="{{ route('kuis.kerjakan', $kuis->id) }}" wire:navigate />
                    @endif
                </x-slot:actions>
            </x-card>
        @empty
            <div class="lg:col-span-3">
                <x-alert title="Tidak ada kuis yang tersedia untuk kelas ini saat ini." />
            </div>
        @endforelse
    </div>
</div>
