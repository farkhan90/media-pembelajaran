<div>
    <x-header title="Hasil & Riwayat Kuis Menjodohkan" separator>
        <x-slot:actions>
            <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                class="btn-sm btn-circle btn-ghost" tooltip-left="Bantuan" />
        </x-slot:actions>
    </x-header>

    {{-- TAMPILAN UNTUK ADMIN DAN GURU --}}
    @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
        <x-card class="mt-8">
            {{-- Hanya tampilkan pencarian untuk Admin/Guru --}}
            @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                <x-input placeholder="Cari nama siswa atau judul kuis..." wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass" class="w-full lg:w-1/3 mb-4" />
            @endif

            @if ($this->hasilKuis()->isNotEmpty())
                <x-table :headers="$headers" :rows="$this->hasilKuis()" with-pagination>
                    @scope('cell_no', $histori)
                        {{ $this->hasilKuis()->firstItem() + $loop->index }}
                    @endscope

                    @scope('cell_user.kelas_info', $histori)
                        @if ($kelasSiswa = $histori->user->kelas->first())
                            {{ $kelasSiswa->nama }} /
                            <span class="text-gray-500">{{ $kelasSiswa->sekolah->nama }}</span>
                        @else
                            <x-badge value="Mandiri" class="badge-ghost" />
                        @endif
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
                            class="btn-sm" />
                    @endscope
                </x-table>
            @else
                <x-alert title="Belum ada data hasil kuis." icon="o-information-circle" />
            @endif
        </x-card>
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

    <x-modal wire:model="bantuanModal" title="Petunjuk Halaman Hasil Kuis">
        <div class="prose max-w-none">
            @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                <p>Halaman ini menampilkan laporan hasil pengerjaan Kuis Menjodohkan oleh siswa.</p>
                <ul>
                    <li><strong>Filter Data:</strong> Pilih <strong>Sekolah</strong>, <strong>Kelas</strong>, lalu
                        <strong>Kuis</strong> untuk melihat data peringkat siswa yang telah mengerjakan.
                    </li>
                    <li><strong>Lihat Rincian:</strong> Klik tombol <x-badge value="Rincian" /> untuk melihat pasangan
                        jawaban yang dibuat oleh siswa dan membandingkannya dengan kunci jawaban.</li>
                    @if (auth()->user()->role === 'Guru')
                        <li><strong>Akses Guru:</strong> Anda hanya dapat melihat hasil kuis dari siswa di kelas yang
                            Anda ampu.</li>
                    @endif
                </ul>
            @else
                {{-- Petunjuk untuk Siswa --}}
                <p>Di halaman ini, kamu bisa melihat semua riwayat pengerjaan Kuis Menjodohkan yang pernah kamu
                    selesaikan.</p>
                <ul>
                    <li><strong>Skor:</strong> Lihat skormu untuk setiap kuis yang telah dikerjakan.</li>
                    <li><strong>Lihat Rincian:</strong> Penasaran pasangan mana yang benar atau salah? Klik tombol
                        <x-badge value="Lihat Rincian" /> untuk melihat kembali hasil pekerjaanmu.
                    </li>
                </ul>
            @endif
        </div>
        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
