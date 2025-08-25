<div>
    <x-header title="Daftar Kuis 1 Tersedia" />

    @if ($kelases->isNotEmpty())
        @foreach ($kelases as $kelas)
            <h2 class="text-xl font-bold mt-6 mb-2">Ujian untuk Kelas: {{ $kelas->nama }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($kelas->ujians()->where('status', 'Published')->get() as $ujian)
                    @php
                        // Cek apakah ada histori untuk ujian ini di collection hasil agregat kita
                        $histori = $historiUjianSiswa->get($ujian->id);
                    @endphp

                    <x-card :title="$ujian->judul" :subtitle="$ujian->deskripsi">
                        <div class="mb-4">
                            {{-- Tampilkan skor tertinggi dari hasil agregat --}}
                            @if ($histori)
                                <div class="text-sm">Skor Terbaik: <span
                                        class="font-bold text-success">{{ round($histori->skor_tertinggi, 2) }}</span>
                                </div>
                                <div class="text-xs text-gray-500">Percobaan: {{ $histori->jumlah_percobaan }}x</div>
                            @else
                                <div class="text-sm">Belum pernah dikerjakan.</div>
                            @endif
                            <div class="text-xs text-gray-500">Waktu: {{ $ujian->waktu_menit }} menit</div>
                        </div>

                        <x-slot:actions>
                            {{-- Logika untuk tombol dinamis tetap sama --}}
                            @if ($histori)
                                <x-button label="Kerjakan Ulang" icon="o-arrow-path" class="btn-secondary"
                                    link="{{ route('ujian.kerjakan', $ujian->slug) }}" wire:navigate />
                            @else
                                <x-button label="Kerjakan Sekarang" icon="o-play" class="btn-primary"
                                    link="{{ route('ujian.kerjakan', $ujian->slug) }}" wire:navigate />
                            @endif
                        </x-slot:actions>
                    </x-card>
                @empty
                    <x-alert title="Tidak ada ujian yang tersedia saat ini." />
                @endforelse
            </div>
        @endforeach
    @else
        <x-alert title="Anda belum terdaftar di kelas manapun." />
    @endif
</div>
