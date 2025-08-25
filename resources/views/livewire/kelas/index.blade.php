<div>
    {{-- HEADER HALAMAN --}}
    <x-header title="Manajemen Kelas" separator>
        <x-slot:actions>
            <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                class="btn-sm btn-circle btn-ghost" tooltip-left="Bantuan" />
            <x-button label="Tambah Kelas" icon="o-plus" wire:click="create" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    {{-- KONTEN UTAMA --}}
    <x-card>
        <div class="mb-4">
            <x-input placeholder="Cari berdasarkan nama kelas atau sekolah..." wire:model.live.debounce.500ms="search"
                icon="o-magnifying-glass" class="w-full lg:w-1/3" />
        </div>

        {{-- TABEL DATA KELAS --}}
        <x-table :headers="$headers" :rows="$kelases" :sort-by="$sortBy" with-pagination>
            @scope('cell_guru_pengampu_nama', $kelas)
                {{-- Tampilkan nama guru atau tanda strip jika kosong --}}
                {{ $kelas->guruPengampu->nama ?? '-' }}
            @endscope

            @scope('actions', $kelas)
                <div class="flex items-center gap-2 justify-end" wire:key="action-{{ $kelas->id }}">
                    {{-- Tombol Edit --}}
                    <x-button icon="o-pencil" wire:click="edit('{{ $kelas->id }}')" class="btn-sm btn-ghost" spinner />

                    {{-- Tombol Hapus --}}
                    <x-button icon="o-trash"
                        wire:click.stop="$dispatch('swal:confirm', {
                            title: 'Yakin ingin menghapus?',
                            text: 'Data kelas akan dihapus permanen!',
                            confirmButtonText: 'Ya, Hapus!',
                            next: {
                                event: 'delete-confirmed',
                                params: { id: '{{ $kelas->id }}' }
                            }
                        })"
                        class="btn-sm btn-ghost text-red-500" spinner />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- MODAL UNTUK TAMBAH/EDIT DATA --}}
    <x-modal wire:model="kelasModal" :title="$isEditMode ? 'Edit Kelas' : 'Tambah Kelas Baru'" separator>
        <x-form wire:submit="save">
            <div class="space-y-4">
                {{-- Dropdown/Select untuk memilih sekolah --}}
                <x-select label="Sekolah" :options="$this->sekolahOptions" wire:model="sekolah_id" placeholder="Pilih Sekolah" />

                <x-input label="Nama Kelas" wire:model="nama" placeholder="Contoh: Kelas X-A" />
                <x-select label="Guru Kelas" :options="$this->guruOptions()" wire:model="guru_pengampu_id"
                    placeholder="Pilih Guru Kelas" option-value="id" option-label="nama" hint="Boleh dikosongkan"
                    allow-empty searchable wire:model.live="guruSearch" />
            </div>

            <x-slot:actions>
                <x-button label="Batal" wire:click="closeModal" />
                <x-button label="Simpan" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="bantuanModal" title="Petunjuk Halaman Manajemen Kelas">
        <div class="prose max-w-none">
            <p>Halaman ini digunakan untuk mengelola data kelas untuk setiap sekolah.</p>
            <ul>
                <li><strong>Filter Sekolah:</strong> Anda harus memilih sekolah terlebih dahulu untuk melihat atau
                    menambah kelas.</li>
                <li><strong>Tambah Kelas:</strong> Setelah sekolah dipilih, klik tombol <x-badge value="+ Tambah Kelas"
                        class="badge-primary" />.</li>
                <li><strong>Guru Kelas:</strong> Saat menambah atau mengedit kelas, Anda bisa menentukan satu guru
                    yang bertanggung jawab atas kelas tersebut.</li>
                <li><strong>Edit & Hapus:</strong> Gunakan ikon <x-icon name="o-pencil" class="inline-block w-4 h-4" />
                    dan <x-icon name="o-trash" class="inline-block w-4 h-4 text-error" /> pada tabel untuk mengelola
                    data.</li>
            </ul>
        </div>
        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
