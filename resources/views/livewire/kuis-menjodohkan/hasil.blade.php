<div>
    <x-header title="Hasil & Riwayat Kuis Menjodohkan" separator />

    {{-- TAMPILAN UNTUK ADMIN DAN GURU --}}
    @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
        <div class="space-y-4 mb-6">
            {{-- Area Filter --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-select label="Pilih Sekolah" :options="$this->sekolahOptions()" wire:model.live="sekolahId"
                    placeholder="-- Semua Sekolah --" option-value="id" option-label="nama" />
                <x-select label="Pilih Kelas" :options="$this->kelasOptions()" wire:model.live="kelasId" placeholder="-- Pilih Kelas --"
                    :disabled="!$sekolahId" option-value="id" option-label="nama" />
                <x-select label="Pilih Kuis" :options="$this->kuisOptions()" wire:model.live="kuisId" placeholder="-- Pilih Kuis --"
                    :disabled="!$kelasId" option-value="id" option-label="judul" />
            </div>
        </div>

        @if ($kuisId)
            <x-card>
                <x-input placeholder="Cari nama siswa..." wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass" class="w-full lg:w-1/3" />

                <x-table :headers="$headers" :rows="$this->hasilKuis" with-pagination>
                    @scope('cell_no', $histori)
                        {{ $this->hasilKuis()->firstItem() + $loop->index }}
                    @endscope
                    @scope('cell_skor_akhir', $histori)
                        <x-badge :value="round($histori->skor_akhir, 2)" @class([
                            'badge-success' => $histori->skor_akhir >= 75,
                            'badge-warning' => $histori->skor_akhir < 75,
                        ]) />
                    @endscope
                    @scope('cell_waktu_selesai', $histori)
                        {{ \Carbon\Carbon::parse($histori->waktu_selesai)->translatedFormat('d M Y, H:i') }}
                    @endscope
                    @scope('actions', $histori)
                        <x-button label="Rincian" icon="o-eye" wire:click="lihatRincian('{{ $histori->id }}')"
                            class="btn-sm btn-ghost" />
                    @endscope
                </x-table>
            </x-card>
        @else
            <x-alert title="Silakan pilih filter di atas untuk melihat hasil kuis." icon="o-information-circle" />
        @endif
    @endif

    {{-- TAMPILAN UNTUK SISWA --}}
    @if (auth()->user()->role === 'Siswa')
        <x-card>
            <x-table :headers="$headers" :rows="$this->hasilKuis" with-pagination>
                @scope('cell_no', $histori)
                    {{ $this->hasilKuis()->firstItem() + $loop->index }}
                @endscope
                @scope('cell_skor_akhir', $histori)
                    <x-badge :value="round($histori->skor_akhir, 2)" @class([
                        'badge-success' => $histori->skor_akhir >= 75,
                        'badge-warning' => $histori->skor_akhir < 75,
                    ]) />
                @endscope
                @scope('cell_waktu_selesai', $histori)
                    {{ \Carbon\Carbon::parse($histori->waktu_selesai)->translatedFormat('d M Y, H:i') }}
                @endscope
                @scope('actions', $histori)
                    <x-button label="Lihat Rincian" icon="o-eye" wire:click="lihatRincian('{{ $histori->id }}')"
                        class="btn-sm" />
                @endscope
            </x-table>
        </x-card>
    @endif

    {{-- MODAL UNTUK RINCIAN JAWABAN KUIS --}}
    @if ($selectedHistori)
        <x-modal wire:model="detailModal" title="Rincian Jawaban: {{ $selectedHistori->user->nama }}"
            subtitle="Kuis: {{ $selectedHistori->kuis->judul }}" separator>
            <div class="overflow-y-auto pr-4 -mr-4 space-y-4">
                @foreach ($detailJawaban as $index => $itemPertanyaan)
                    @php
                        // Cari jawaban siswa untuk item pertanyaan ini
                        $jawabanSiswa = \App\Models\ItemJawaban::find($itemPertanyaan->jawaban_siswa_item_jawaban_id);
                        // Cek apakah jawaban siswa benar
                        $isBenar = $jawabanSiswa && $jawabanSiswa->item_pertanyaan_id == $itemPertanyaan->id;
                    @endphp
                    <div class="p-4 rounded-lg {{ $loop->odd ? 'bg-base-200' : 'bg-base-100' }}"
                        wire:key="detail-kuis-{{ $itemPertanyaan->id }}">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            {{-- Kolom Pertanyaan --}}
                            <div class="col-span-5 text-center p-2 border rounded-lg">
                                @if ($itemPertanyaan->tipe_item === 'Teks')
                                    <p>{{ $itemPertanyaan->konten }}</p>
                                @else
                                    <img src="..." class="mx-auto">
                                @endif
                            </div>
                            {{-- Indikator Benar/Salah --}}
                            <div class="col-span-2 text-center">
                                @if ($isBenar)
                                    <x-icon name="o-check-circle" class="w-8 h-8 text-success mx-auto" />
                                @else
                                    <x-icon name="o-x-circle" class="w-8 h-8 text-error mx-auto" />
                                @endif
                            </div>
                            {{-- Kolom Jawaban Siswa --}}
                            <div
                                class="col-span-5 text-center p-2 border rounded-lg {{ $isBenar ? 'border-success' : 'border-error' }}">
                                @if ($jawabanSiswa)
                                    @if ($jawabanSiswa->tipe_item === 'Teks')
                                        <p>{{ $jawabanSiswa->konten }}</p>
                                    @else
                                        <img src="..." class="mx-auto">
                                    @endif
                                @else
                                    <p class="text-gray-500 italic">Tidak Dijawab</p>
                                @endif
                            </div>
                        </div>
                        {{-- Tampilkan jawaban yang benar jika jawaban siswa salah --}}
                        @if (!$isBenar)
                            <div class="text-xs text-center mt-2 text-success">
                                Jawaban yang benar: <strong>{{ $itemPertanyaan->itemJawaban->konten }}</strong>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <x-slot:actions>
                <x-button label="Tutup" @click="$wire.detailModal = false" />
            </x-slot:actions>
        </x-modal>
    @endif
</div>
