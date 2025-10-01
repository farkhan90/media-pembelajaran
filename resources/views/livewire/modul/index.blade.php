<div>
    <x-header title="Manajemen Modul (Pulau)" separator>
        <x-slot:actions>
            <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                class="btn-sm btn-circle btn-ghost" tooltip-left="Bantuan" />
            <x-button label="Tambah Modul" icon="o-plus" wire:click="create" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card class="mt-4">
        {{-- Tabel untuk menampilkan daftar modul --}}
        <x-table :headers="$headers" :rows="$moduls" with-pagination>
            @scope('cell_judul', $modul)
                <a href="{{ route('modul.langkah.index', ['modul' => $modul->id]) }}" class="font-bold hover:underline"
                    wire:navigate>
                    {{ $modul->judul }}
                </a>
            @endscope
            @scope('cell_gambar_pulau', $modul)
                @if ($modul->gambar_pulau)
                    <div class="flex justify-center">
                        <img src="{{ route('modul.pulau.gambar', ['modulId' => $modul->id]) }}"
                            alt="Gambar {{ $modul->nama_pulau }}" class="w-20 h-12 object-contain">
                    </div>
                @else
                    <div class="flex justify-center text-gray-300">
                        <x-icon name="o-photo" class="w-8 h-8" />
                    </div>
                @endif
            @endscope
            @scope('cell_is_published', $modul)
                @if ($modul->is_published)
                    <x-badge value="Published" class="badge-success" />
                @else
                    <x-badge value="Draft" class="badge-warning" />
                @endif
            @endscope
            @scope('actions', $modul)
                <div class="flex items-center gap-2 justify-end">
                    <x-button icon="o-pencil" wire:click="edit('{{ $modul->id }}')" class="btn-sm" />
                    <x-button icon="o-trash"
                        wire:click="$dispatch('swal:confirm', {
                            title: 'Yakin ingin menghapus?',
                            text: 'Menghapus modul akan menghapus SEMUA langkah dan progres siswa di dalamnya secara permanen!',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal',
                            next: {
                                event: 'delete-confirmed',
                                params: { modulId: '{{ $modul->id }}' }
                            }
                        })"
                        class="btn-sm btn-ghost text-red-500" spinner />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Modal untuk form create/edit modul --}}
    <x-modal wire:model="modulModal" title="{{ $isEditMode ? 'Edit Modul' : 'Tambah Modul Baru' }}">
        <x-form wire:submit="save">
            <x-input label="Judul Modul" wire:model="judul" />
            <x-input label="Nama Pulau (ID)" wire:model="nama_pulau"
                hint="Gunakan huruf kecil tanpa spasi, cth: sumatera" />
            <x-input label="Urutan" wire:model="urutan" type="number" />
            <x-file label="Gambar Pulau (SVG)" wire:model="gambar_pulau" />
            <x-toggle label="Published" wire:model="is_published" />

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.modulModal = false" />
                <x-button label="Simpan" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal bantuan --}}
    <x-modal wire:model="bantuanModal" title="Petunjuk Halaman Manajemen Modul">
        <div class="prose max-w-none">
            <p>Halaman ini adalah tempat Anda membuat dan mengatur "Pulau-pulau Petualangan" utama yang akan dilihat
                siswa di halaman peta.</p>

            <h4>Fungsi Utama:</h4>
            <ul>
                <li>
                    <strong>Tambah Modul:</strong> Gunakan tombol <x-badge value="+ Tambah Modul"
                        class="badge-primary" /> untuk membuat pulau baru.
                </li>
                <li>
                    <strong>Mengatur Urutan:</strong> Kolom <x-badge value="#" /> menunjukkan urutan pulau di peta.
                    Anda bisa mengubah nomor ini saat mengedit untuk mengatur ulang alur petualangan siswa.
                </li>
                <li>
                    <strong>Nama Pulau (ID):</strong> Ini adalah pengidentifikasi unik untuk sistem (contoh:
                    `sumatera`). <strong>Jangan diubah</strong> setelah dibuat jika sudah ada progres siswa yang
                    terkait.
                </li>
                <li>
                    <strong>Status:</strong>
                    <ul>
                        <li><x-badge value="Published" class="badge-success" />: Modul/pulau akan terlihat oleh semua
                            pengguna di Peta Petualangan.</li>
                        <li><x-badge value="Draft" class="badge-warning" />: Modul/pulau disembunyikan dan hanya
                            terlihat oleh Admin.</li>
                    </ul>
                </li>
                <li>
                    <strong>Mengisi Konten:</strong> Setelah sebuah modul dibuat, **klik pada judulnya** di tabel untuk
                    masuk ke halaman "Manajemen Langkah" dan mulai menambahkan video, materi, dan soal.
                </li>
            </ul>
        </div>
        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
