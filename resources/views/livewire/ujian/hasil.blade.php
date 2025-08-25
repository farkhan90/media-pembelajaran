<div>
    <x-header title="Hasil & Riwayat Kuis Pilgan" separator>
        <x-slot:actions>
            <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                class="btn-sm btn-circle btn-ghost" tooltip-left="Bantuan" />
        </x-slot:actions>
    </x-header>

    {{-- TAMPILAN UNTUK ADMIN DAN GURU --}}
    @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
        <div class="space-y-4 mb-6">
            {{-- Area Filter --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-select label="Pilih Sekolah" :options="$this->sekolahOptions()" wire:model.live="sekolahId" option-value="id"
                    option-label="nama" placeholder="-- Semua Sekolah --" />
                <x-select label="Pilih Kelas" :options="$this->kelasOptions()" wire:model.live="kelasId"
                    placeholder="-- Pilih Kelas --" option-value="id" option-label="nama" :disabled="!$sekolahId" />
                <x-select label="Pilih Kuis 1" :options="$this->ujianOptions()" wire:model.live="ujianId"
                    placeholder="-- Pilih Kuis 1 --" :disabled="!$kelasId" option-value="id" option-label="judul" />
            </div>
        </div>

        @if ($ujianId)
            <x-card>
                <x-input placeholder="Cari nama siswa..." wire:model.live.debounce.300ms="search"
                    icon="o-magnifying-glass" class="w-full lg:w-1/3" />

                <x-table :headers="$headers" :rows="$this->hasilUjians" with-pagination>
                    @scope('cell_no', $histori)
                        {{ $this->hasilUjians()->firstItem() + $loop->index }}
                    @endscope
                    @scope('cell_skor_akhir', $histori)
                        <x-badge :value="round($histori->skor_akhir, 2)" @class([
                            'badge-success' => $histori->skor_akhir >= 75,
                            'badge-warning' => $histori->skor_akhir < 75,
                        ]) />
                    @endscope
                    @scope('cell_waktu_mulai', $histori)
                        {{ \Carbon\Carbon::parse($histori->waktu_mulai)->translatedFormat('d M Y, H:i') }}
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
            <x-alert title="Silakan pilih filter di atas untuk melihat hasil ujian." icon="o-information-circle" />
        @endif
    @endif

    {{-- TAMPILAN UNTUK SISWA --}}
    @if (auth()->user()->role === 'Siswa')
        <x-card>
            <x-table :headers="$headers" :rows="$this->hasilUjians" with-pagination>
                @scope('cell_no', $histori)
                    {{ $this->hasilUjians()->firstItem() + $loop->index }}
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

    {{-- MODAL UNTUK RINCIAN JAWABAN --}}
    @if ($selectedHistori)
        <x-modal wire:model="detailModal" title="Rincian Jawaban: {{ $selectedHistori->user->nama }}"
            subtitle="Ujian: {{ $selectedHistori->ujian->judul }}" separator>
            <div class="h-[70vh] overflow-y-auto pr-4 -mr-4 space-y-4">
                @foreach ($detailJawaban as $index => $soal)
                    <div class="p-4 rounded-lg {{ $loop->odd ? 'bg-base-200' : 'bg-base-100' }}"
                        wire:key="detail-{{ $soal->id }}">
                        <div class="prose max-w-none">
                            <p class="font-semibold">Soal #{{ $index + 1 }}:</p>
                            @if ($soal->gambar_soal)
                                <div class="mt-4 flex justify-center">
                                    <img src="{{ route('files.soal.gambar', ['soalId' => $soal->id]) }}"
                                        class="max-w-[150px] rounded-lg shadow-md" alt="Gambar Soal">
                                </div>
                            @endif
                            <p>{!! nl2br(e($soal->pertanyaan)) !!}</p>
                        </div>
                        <div class="mt-3 space-y-2">
                            @foreach ($soal->opsiJawabans as $opsi)
                                @php
                                    $isJawabanSiswa = $soal->jawaban_siswa_opsi_id == $opsi->id;
                                    $isJawabanBenar = $opsi->is_benar;
                                @endphp
                                <div @class([
                                    'flex items-center gap-3 p-2 rounded-md border',
                                    'border-success bg-green-100 text-green-800' => $isJawabanBenar,
                                    'border-error bg-red-100 text-red-800' =>
                                        $isJawabanSiswa && !$isJawabanBenar,
                                    'border-base-300' => !$isJawabanSiswa && !$isJawabanBenar,
                                ])>
                                    @if ($isJawabanBenar)
                                        <x-icon name="o-check-circle" class="w-5 h-5 text-success" />
                                    @endif
                                    @if ($isJawabanSiswa && !$isJawabanBenar)
                                        <x-icon name="o-x-circle" class="w-5 h-5 text-error" />
                                    @endif

                                    <span>{{ $opsi->teks_opsi }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <x-slot:actions>
                <x-button label="Tutup" @click="$wire.detailModal = false" />
            </x-slot:actions>
        </x-modal>
    @endif

    <x-modal wire:model="bantuanModal" title="Petunjuk Halaman Hasil Ujian">
        <div class="prose max-w-none">
            @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
                <p>Halaman ini menampilkan laporan hasil pengerjaan Ujian Pilihan Ganda oleh siswa. Gunakan filter di
                    atas untuk menampilkan data yang spesifik.</p>
                <ul>
                    <li><strong>Filter Data:</strong> Pilih <strong>Sekolah</strong>, <strong>Kelas</strong>, lalu
                        <strong>Ujian</strong> untuk melihat grafik dan tabel peringkat siswa yang telah mengerjakan.
                    </li>
                    <li><strong>Grafik Skor:</strong> Grafik batang menampilkan sebaran skor semua siswa yang telah
                        menyelesaikan ujian yang dipilih, diurutkan dari yang tertinggi.</li>
                    <li><strong>Lihat Rincian:</strong> Klik tombol <x-badge value="Rincian" /> pada setiap baris siswa
                        untuk membuka modal yang menampilkan detail jawaban siswa (jawaban benar dan jawaban yang
                        dipilih).</li>
                    @if (auth()->user()->role === 'Guru')
                        <li><strong>Akses Guru:</strong> Anda hanya dapat melihat hasil ujian dari siswa di kelas yang
                            Anda ampu.</li>
                    @endif
                </ul>
            @else
                {{-- Petunjuk untuk Siswa --}}
                <p>Di halaman ini, kamu bisa melihat semua riwayat pengerjaan Ujian Pilihan Ganda yang pernah kamu
                    selesaikan.</p>
                <ul>
                    <li><strong>Skor:</strong> Lihat skormu untuk setiap ujian yang telah dikerjakan.</li>
                    <li><strong>Lihat Rincian:</strong> Ingin tahu jawaban mana yang salah? Klik tombol <x-badge
                            value="Lihat Rincian" /> untuk melihat kembali soal dan jawabanmu.</li>
                </ul>
                <p>Gunakan halaman ini untuk belajar dari kesalahan dan menjadi lebih pintar lagi!</p>
            @endif
        </div>
        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
