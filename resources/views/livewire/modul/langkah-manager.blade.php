<div>
    <x-header title="Manajemen Langkah" :subtitle="$modul->judul" separator>
        <x-slot:before>
            <a href="{{ route('modul.index') }}" wire:navigate>
                <x-button label="Kembali ke Daftar Modul" icon="o-arrow-left" class="btn-ghost" />
            </a>
        </x-slot:before>
        <x-slot:actions>
            <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                class="btn-sm btn-circle btn-ghost" tooltip-left="Bantuan" />
            <x-button label="Tambah Langkah" icon="o-plus" wire:click="create" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card class="mt-4">
        {{-- Tabel untuk menampilkan daftar langkah --}}
        <x-table :headers="$headers" :rows="$langkahs" with-pagination>

            @scope('cell_judul', $langkah)
                <div class="font-bold hover:underline">{{ $langkah->judul }}</div>
                {{-- Tampilkan keterangan di bawah judul --}}
                <div class="text-xs text-gray-500">{{ Str::limit($langkah->keterangan, 50) }}</div>
            @endscope

            {{-- Scope untuk Tipe Konten --}}
            @scope('cell_tipe', $langkah)
                @php
                    $colors = [
                        'video' => 'badge-info',
                        'audio' => 'badge-info',
                        'canva' => 'badge-secondary',
                        'pdf' => 'badge-secondary',
                        'soal_esai' => 'badge-warning',
                        'penilaian_akhir' => 'badge-primary',
                    ];
                @endphp
                <x-badge :value="Str::title(str_replace('_', ' ', $langkah->tipe))" class="{{ $colors[$langkah->tipe] ?? 'badge-ghost' }}" />
            @endscope

            {{-- Scope untuk Detail Konten --}}
            @scope('cell_detail', $langkah)
                @if (in_array($langkah->tipe, ['video', 'audio', 'canva', 'pdf']))
                    <a href="{{ $langkah->konten_path }}" target="_blank" class="link link-primary truncate">
                        {{ Str::limit($langkah->konten_path, 40) }}
                    </a>
                @elseif($langkah->tipe === 'soal_esai')
                    <div class="prose prose-sm max-w-md line-clamp-2">
                        {!! $langkah->konten_teks !!}
                    </div>
                @elseif($langkah->tipe === 'penilaian_akhir')
                    <div class="text-xs">
                        @if ($langkah->ujian)
                            <div><strong>Pilgan:</strong> {{ $langkah->ujian->judul }}</div>
                        @endif
                        @if ($langkah->kuisMenjodohkan)
                            <div><strong>Menjodohkan:</strong> {{ $langkah->kuisMenjodohkan->judul }}</div>
                        @endif
                    </div>
                @else
                    -
                @endif
            @endscope

            {{-- Scope untuk Aksi (Edit & Hapus) --}}
            @scope('actions', $langkah)
                <div class="flex items-center gap-2 justify-end">
                    <x-button icon="o-pencil" wire:click="edit('{{ $langkah->id }}')" class="btn-sm btn-ghost" spinner />
                    <x-button icon="o-trash"
                        wire:click="$dispatch('swal:confirm', {
                        title: 'Yakin ingin menghapus?',
                        text: 'Menghapus langkah ini tidak dapat dibatalkan!',
                        next: { event: 'delete-confirmed', params: { id: '{{ $langkah->id }}' } }
                    })"
                        class="btn-sm btn-ghost text-red-500" spinner />
                </div>
            @endscope

        </x-table>
    </x-card>

    <x-modal wire:model="langkahModal" title="{{ $isEditMode ? 'Edit Langkah' : 'Tambah Langkah Baru' }}">
        <x-form wire:submit="save">
            <div class="space-y-4">
                <x-input label="Judul Langkah" wire:model="judul" />
                <x-textarea label="Keterangan Singkat (Opsional)" wire:model="keterangan" rows="3"
                    hint="Akan ditampilkan sebagai subjudul di halaman player siswa." />
                <x-input label="Urutan" wire:model="urutan" type="number" />
                <x-select label="Tipe Konten" :options="$tipeOptions" wire:model.live="tipe" />

                <hr class="my-4">

                {{-- KONTEN DINAMIS BERDASARKAN TIPE --}}
                @if (in_array($tipe, ['video', 'audio', 'canva', 'pdf']))
                    <x-input label="URL atau Path File" wire:model="konten_path"
                        hint="Contoh: https://youtube.com/embed/... atau materi/bab1.pdf" />
                @endif

                @if ($tipe === 'soal_esai')
                    <div>
                        {{-- Gunakan komponen x-editor dari MaryUI --}}
                        <x-editor wire:model.live="konten_teks" label="Pertanyaan Esai" :config="['height' => 150]"
                            placeholder="Tulis pertanyaan Anda di sini..." />

                        @error('konten_teks')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($tipe === 'penilaian_akhir')
                    <div class="space-y-4">
                        <x-select label="Pilih Ujian Pilihan Ganda" :options="$this->ujianOptions()" wire:model="ujian_id"
                            option-value="id" option-label="judul" />
                        <x-select label="Pilih Kuis Menjodohkan" :options="$this->kuisOptions()" wire:model="kuis_menjodohkan_id"
                            option-value="id" option-label="judul" />
                    </div>
                @endif

                <hr class="my-4">

                {{-- KONDISI SELESAI DINAMIS --}}
                <x-select label="Kondisi Selesai" :options="$kondisiOptions" wire:model.live="kondisi_selesai_tipe" />

                @if ($kondisi_selesai_tipe === 'timer')
                    <x-input label="Durasi Timer (detik)" wire:model="kondisi_selesai_detik" type="number" />
                @endif
            </div>

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.langkahModal = false" />
                <x-button label="Simpan Langkah" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="bantuanModal" title="Petunjuk Halaman Manajemen Langkah">
        <div class="prose max-w-none">
            <p>Di halaman ini, Anda menyusun aktivitas atau "langkah" yang harus diselesaikan siswa di dalam sebuah
                modul/pulau.</p>

            <h4>Form Tambah/Edit Langkah:</h4>
            <ul>
                <li>
                    <strong>Tipe Konten:</strong> Ini adalah bagian terpenting. Pilihan Anda di sini akan mengubah form
                    di bawahnya secara dinamis.
                    <ul>
                        <li><strong>Video, Audio, Canva, PDF:</strong> Anda akan diminta untuk memasukkan URL atau path
                            file. Pastikan file sudah diunggah ke lokasi yang benar (contoh:
                            `public/materi_pdf/namafile.pdf`).</li>
                        <li><strong>Soal Esai:</strong> Akan muncul sebuah editor teks untuk Anda menulis pertanyaan.
                        </li>
                        <li><strong>Penilaian Akhir:</strong> Anda akan diminta untuk memilih dari Ujian dan Kuis yang
                            sudah Anda buat di "Bank Soal".</li>
                    </ul>
                </li>
                <li>
                    <strong>Kondisi Selesai:</strong> Atur bagaimana siswa bisa menyelesaikan langkah ini.
                    <ul>
                        <li><strong>Otomatis:</strong> Tombol "Lanjut" akan aktif setelah video/audio selesai.</li>
                        <li><strong>Timer:</strong> Tombol "Lanjut" akan aktif setelah siswa berada di halaman selama
                            durasi yang Anda tentukan (dalam detik). Cocok untuk materi baca.</li>
                        <li><strong>Submit Form:</strong> Tombol "Lanjut" akan aktif setelah siswa mengirimkan jawaban
                            esai yang valid.</li>
                    </ul>
                </li>
            </ul>
            <p><strong>Urutan</strong> sangat penting. Siswa akan melalui langkah-langkah ini sesuai dengan nomor urut
                yang Anda tentukan.</p>
        </div>
        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
