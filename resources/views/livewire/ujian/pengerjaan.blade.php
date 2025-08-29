<div class="bg-indigo-100" x-data="{
    sisaWaktu: {{ (int) max(0, $ujian->waktu_menit * 60 - now()->diffInSeconds($histori->waktu_mulai)) }},
    timer: null,
    init() {
        if (this.sisaWaktu <= 0) return;
        this.timer = setInterval(() => {
            this.sisaWaktu--;
            if (this.sisaWaktu <= 0) {
                clearInterval(this.timer);
                // Beri sedikit jeda sebelum memanggil server untuk memastikan UI terupdate
                setTimeout(() => $wire.selesaikanUjian(), 500);
            }
        }, 1000);
    },
    formatWaktu() {
        // Jika waktu sudah habis, tampilkan 00:00
        if (this.sisaWaktu <= 0) {
            return '00:00';
        }

        // Gunakan Math.floor() untuk memastikan hasilnya selalu integer
        let minutes = Math.floor(this.sisaWaktu / 60);
        let seconds = this.sisaWaktu % 60;

        // Gunakan padStart untuk menambahkan '0' di depan jika angkanya < 10
        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
}" x-init="init()">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-full p-4 ">

        {{-- Kolom Utama (Soal) --}}
        <div class="lg:col-span-9 flex flex-col">
            <div class="bg-base-100 p-6 rounded-lg shadow-md flex-grow flex flex-col">
                {{-- Header Soal --}}
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Soal #{{ $soalIndex + 1 }}</h2>
                    {{-- Fitur Ragu-ragu (opsional) --}}
                </div>

                {{-- Konten Soal --}}
                <div class="prose max-w-none flex-grow">
                    @if ($soals[$soalIndex]->gambar_soal)
                        <div class="mt-4 flex justify-center">
                            <img src="{{ route('files.soal.gambar', ['soalId' => $soals[$soalIndex]->id]) }}"
                                class="max-w-[300px] rounded-lg shadow-md" alt="Gambar Soal">
                        </div>
                    @endif
                    @if ($soals->has($soalIndex))
                        {!! nl2br(e($soals[$soalIndex]->pertanyaan)) !!}
                    @endif
                </div>

                {{-- Opsi Jawaban --}}
                <div class="mt-6 space-y-3">
                    @if ($soals->has($soalIndex))
                        @foreach ($soals[$soalIndex]->opsiJawabans as $index => $opsi)
                            <label wire:key="opsi-{{ $opsi->id }}"
                                class="flex items-center gap-4 p-4 rounded-lg border-2 cursor-pointer transition"
                                :class="{
                                    'border-primary bg-blue-50': '{{ @$jawabanSiswa[$soals[$soalIndex]->id] }}' ==
                                        '{{ $opsi->id }}',
                                    'border-base-300 hover:border-gray-400': '{{ @$jawabanSiswa[$soals[$soalIndex]->id] }}' !=
                                        '{{ $opsi->id }}'
                                }">
                                <input type="radio" name="jawaban_radio_{{ $soals[$soalIndex]->id }}"
                                    wire:model.live="jawabanSoalAktif" value="{{ $opsi->id }}"
                                    class="radio radio-primary">
                                <span class="font-bold text-lg">{{ chr(65 + $index) }}</span>
                                <div class="flex-grow">{{ $opsi->teks_opsi }}</div>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Samping (Navigasi & Info) --}}
        <div class="lg:col-span-3 flex flex-col">
            <div class="bg-base-100 p-4 rounded-lg shadow-md mb-4">
                <div class="text-center">
                    <div class="text-sm">Sisa Waktu</div>
                    <div class="text-3xl font-bold text-error" x-text="formatWaktu()"></div>
                </div>
            </div>

            <div class="bg-base-100 p-4 rounded-lg shadow-md flex-grow mb-4">
                <h3 class="font-bold mb-4">Navigasi Soal</h3>
                <div class="grid grid-cols-5 gap-2">
                    @foreach ($soals as $index => $soal)
                        <button wire:click="goToSoal({{ $index }})" @class([
                            'btn btn-sm',
                            'btn-primary' => $soalIndex == $index,
                            'btn-success' => isset($jawabanSiswa[$soal->id]) && $soalIndex != $index,
                            'btn-outline' => !isset($jawabanSiswa[$soal->id]) && $soalIndex != $index,
                        ])>
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="bg-base-100 rounded-lg shadow-md mt-auto"> {{-- 'mt-auto' mendorong ke bawah jika ada ruang --}}

                {{-- Tampilkan tombol 'Soal Berikutnya' jika BUKAN soal terakhir --}}
                @if ($soalIndex < $soals->count() - 1)
                    <x-button label="Soal Berikutnya" wire:click="nextSoal" icon-right="o-arrow-right"
                        class="btn-primary btn-block" wire:key="tombol-next" />
                @endif

                {{-- Tampilkan tombol 'Selesaikan Ujian' HANYA di soal terakhir --}}
                @if ($soalIndex == $soals->count() - 1)
                    <x-button label="Selesaikan Ujian" class="btn-success btn-block" icon-right="o-check-circle"
                        wire:click="openRekapModal" wire:key="tombol-selesai" />
                @endif
            </div>
        </div>

    </div>
    {{-- MODAL REKAP SEBELUM SELESAI UJIAN --}}
    <x-modal wire:model="rekapModal" title="Konfirmasi Selesaikan Ujian">
        <div class="space-y-4">
            {{-- Ringkasan Jawaban --}}
            <div class="text-center">
                <p class="text-lg">Anda telah menjawab</p>
                <p class="text-4xl font-bold">
                    {{ $this->jumlahSoalDijawab() }} <span class="text-xl font-normal">/
                        {{ $this->jumlahSoalTotal() }}
                        soal</span>
                </p>
            </div>

            {{-- Peringatan jika ada soal kosong --}}
            @if ($this->adaSoalKosong())
                <x-alert title="Peringatan!" icon="o-exclamation-triangle" class="alert-warning">
                    Masih ada soal yang belum Anda jawab. Anda masih bisa kembali untuk memeriksanya.
                    <br>
                    Apakah Anda tetap yakin ingin menyelesaikan ujian ini?
                </x-alert>
            @else
                <x-alert title="Semua soal telah dijawab!" icon="o-check-circle" class="alert-success">
                    Anda sudah menjawab semua soal. Apakah Anda siap untuk melihat hasilnya?
                </x-alert>
            @endif

            <p class="text-sm text-center text-gray-500">
                Setelah ujian diselesaikan, Anda tidak akan bisa mengubah jawaban Anda lagi.
            </p>
        </div>

        <x-slot:actions>
            {{-- Tombol untuk kembali (hanya menutup modal) --}}
            <x-button label="Kembali & Periksa Lagi" @click="$wire.rekapModal = false" />

            {{-- Tombol konfirmasi akhir yang memanggil backend --}}
            <x-button label="Ya, Selesaikan" class="btn-primary" wire:click="selesaikanUjian"
                spinner="selesaikanUjian" />
        </x-slot:actions>
    </x-modal>
</div>
