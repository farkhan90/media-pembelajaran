<div>
    {{-- HEADER HALAMAN --}}
    <x-header title="Manajemen Sekolah" separator>
        <x-slot:actions>
            <x-button label="Tambah Sekolah" icon="o-plus" wire:click="create" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    {{-- KONTEN UTAMA --}}
    <x-card>
        <div class="mb-4">
            <x-input placeholder="Cari berdasarkan nama atau NPSN..." wire:model.live.debounce.500ms="search"
                icon="o-magnifying-glass" class="w-full lg:w-1/3 pr-2 placeholder:text-sm" />
        </div>

        {{-- TABEL DATA SEKOLAH --}}
        <x-table :headers="$headers" :rows="$sekolahs" :sort-by="$sortBy" with-pagination>

            {{-- Scope untuk menampilkan logo --}}
            @scope('cell_logo', $sekolah)
                @if ($sekolah->logo)
                    <img src="{{ route('files.sekolah.logo', ['sekolahId' => $sekolah->id]) }}"
                        alt="Logo {{ $sekolah->nama }}" class="h-14 w-14 object-contain rounded-md">
                @else
                    <div class="flex items-center justify-center h-14 w-14 bg-gray-200 rounded-md">
                        <x-icon name="o-photo" class="text-gray-400" />
                    </div>
                @endif
            @endscope

            @scope('actions', $sekolah)
                {{-- TAMBAHKAN wire:key DI SINI --}}
                <div class="flex items-center gap-2 justify-end">

                    {{-- Tombol Edit --}}
                    <x-button icon="o-pencil" wire:click="edit('{{ $sekolah->id }}')" class="btn-sm btn-ghost" spinner />

                    {{-- Tombol Hapus --}}
                    <x-button icon="o-trash"
                        wire:click.stop="$dispatch('swal:confirm', {
                title: 'Yakin ingin menghapus?',
                text: 'Data Sekolah & semua data terkait akan dihapus permanen!',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                next: {
                    event: 'delete-confirmed',
                    params: { id: '{{ $sekolah->id }}' }
                }
            })"
                        class="btn-sm btn-ghost text-red-500" spinner />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- MODAL UNTUK TAMBAH/EDIT DATA DENGAN TAMPILAN UPLOAD BARU --}}
    <x-modal wire:model="sekolahModal" :title="$isEditMode ? 'Edit Sekolah' : 'Tambah Sekolah Baru'" separator>
        <x-form wire:submit.prevent="save">
            <div class="space-y-4">
                <x-input label="Nama Sekolah" wire:model="nama" />
                <x-input label="NPSN" wire:model="npsn" />
                <x-textarea label="Alamat" wire:model="alamat" rows="3" />
                <div>
                    <label class="label" for="logo-upload">
                        <span class="label-text">Logo Sekolah</span>
                    </label>
                    @if ($this->logoPreviewUrl())
                        <div class="relative group w-32 h-32 mx-auto">
                            <img src="{{ $this->logoPreviewUrl() }}"
                                class="w-32 h-32 object-contain rounded-lg shadow-md">
                            <div wire:click="removeLogo"
                                class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1.5 cursor-pointer opacity-100 group-hover:opacity-100 transition-opacity">
                                <x-icon name="o-x-mark" class="w-4 h-4" />
                            </div>
                        </div>
                    @else
                        <div x-data="{ dragging: false }" @dragover.prevent="dragging = true"
                            @dragleave.prevent="dragging = false"
                            @drop.prevent="dragging = false; $wire.upload('logo', $event.dataTransfer.files[0])"
                            :class="{ 'border-primary bg-blue-50': dragging }"
                            class="w-full border-2 border-dashed rounded-lg p-6 text-center hover:bg-gray-50 transition-colors">
                            <label for="logo-upload" class="cursor-pointer">
                                <div wire:loading.remove wire:target="logo">
                                    <x-icon name="o-cloud-arrow-up" class="w-12 h-12 mx-auto text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-600">
                                        <span class="font-semibold text-primary">Klik untuk upload</span> atau seret
                                        file ke sini
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG (Maks. 2MB)</p>
                                </div>

                                <div wire:loading wire:target="logo">
                                    <x-loading class="mx-auto text-primary" />
                                    <p class="mt-2 text-sm text-gray-600">Uploading...</p>
                                </div>
                            </label>

                            <input id="logo-upload" type="file" wire:model="logo" class="hidden"
                                accept="image/png, image/jpeg">

                            @error('logo')
                                <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif
                </div>

            </div>

            <x-slot:actions>
                <x-button label="Batal" wire:click="closeModal" />
                <x-button label="Simpan" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
