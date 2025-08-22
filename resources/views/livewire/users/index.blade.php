<div>
    <x-header title="Manajemen User" separator>
        <x-slot:actions>
            <x-button label="Download Template" icon="o-document-arrow-down" wire:click="downloadTemplate"
                class="btn-outline" />
            <x-button label="Impor User" icon="o-document-arrow-up" @click="$wire.imporModal = true" class="btn-primary" />
            <x-button label="Tambah User" icon="o-plus" wire:click="create" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-input placeholder="Cari berdasarkan nama atau email..." wire:model.live.debounce.500ms="search"
            icon="o-magnifying-glass" class="w-full lg:w-1/3" />

        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination class="mt-3">
            @scope('cell_foto', $user)
                @if ($user->foto)
                    <x-avatar :image="route('files.user.foto', ['userId' => $user->id])" class="!w-14 !h-14" />
                @else
                    <div class="flex items-center justify-center h-14 w-14 bg-gray-200 rounded-md">
                        <x-icon name="o-photo" class="text-gray-400" />
                    </div>
                @endif
            @endscope

            @scope('cell_role', $user)
                <x-badge :value="$user->role" @class([
                    'badge-primary' => $user->role === 'Admin',
                    'badge-success' => $user->role === 'Guru',
                    'badge-info' => $user->role === 'Siswa',
                ]) />
            @endscope

            @scope('actions', $user)
                <div class="flex items-center gap-2 justify-end" wire:key="action-{{ $user->id }}">
                    <x-button icon="o-pencil" wire:click="edit('{{ $user->id }}')" class="btn-sm btn-ghost" spinner />
                    <x-button icon="o-trash"
                        wire:click.stop="$dispatch('swal:confirm', {
                            title: 'Yakin ingin menghapus?',
                            text: 'User ini akan dihapus permanen!',
                            next: { event: 'delete-confirmed', params: { id: '{{ $user->id }}' } }
                        })"
                        class="btn-sm btn-ghost text-red-500" spinner />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="userModal" :title="$isEditMode ? 'Edit User' : 'Tambah User Baru'" separator>
        <x-form wire:submit="save">
            <div class="mb-4">
                <label class="label"><span class="label-text">Foto Profil</span></label>
                @if ($this->fotoPreviewUrl())
                    <div class="relative group w-24 h-24 mx-auto">
                        <img src="{{ $this->fotoPreviewUrl() }}" class="w-24 h-24 object-cover rounded-full shadow-md">
                        <div wire:click="removeFoto"
                            class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white rounded-full p-1.5 cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity">
                            <x-icon name="o-x-mark" class="w-4 h-4" />
                        </div>
                    </div>
                @else
                    <div x-data="{ dragging: false }" @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false; $wire.upload('foto', $event.dataTransfer.files[0])"
                        :class="{ 'border-primary bg-blue-50': dragging }"
                        class="w-full border-2 border-dashed rounded-lg p-6 text-center hover:bg-gray-50 transition-colors">
                        <label for="foto-upload" class="cursor-pointer">
                            <div wire:loading.remove wire:target="foto">
                                <x-icon name="o-photo" class="w-12 h-12 mx-auto text-gray-400" />
                                <p class="mt-2 text-sm text-gray-600">
                                    <span class="font-semibold text-primary">Klik untuk upload</span> atau seret file
                                </p>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG (Maks. 1MB)</p>
                            </div>
                            <div wire:loading wire:target="foto"><x-loading class="mx-auto" /></div>
                        </label>
                        <input id="foto-upload" type="file" wire:model="foto" class="hidden"
                            accept="image/png, image/jpeg">
                        @error('foto')
                            <span class="text-red-500 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Lengkap" wire:model="nama" />
                    <x-input label="Email" wire:model="email" type="email" />
                    <x-select label="Role" :options="[
                        ['id' => 'Admin', 'name' => 'Admin'],
                        ['id' => 'Guru', 'name' => 'Guru'],
                        ['id' => 'Siswa', 'name' => 'Siswa'],
                    ]" wire:model="role" />
                    <x-radio label="Jenis Kelamin" :options="[['id' => 'L', 'name' => 'Laki-laki'], ['id' => 'P', 'name' => 'Perempuan']]" wire:model="jk" />
                    <x-input label="Password" wire:model="password" type="password" :placeholder="$isEditMode ? 'Kosongkan jika tidak diubah' : ''" />
                    <x-input label="Konfirmasi Password" wire:model="password_confirmation" type="password" />
                </div>

                <x-slot:actions>
                    <x-button label="Batal" wire:click="closeModal" />
                    <x-button label="Simpan" type="submit" class="btn-primary" spinner="save" />
                </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- MODAL BARU UNTUK IMPOR --}}
    <x-modal wire:model="imporModal" title="Impor Data User">
        <div class="space-y-4">
            <p>Unggah file Excel yang sudah diisi sesuai dengan template. Jika email sudah ada, data akan diperbarui
                (kecuali foto). Jika belum ada, user baru akan dibuat.</p>

            <x-file label="File Excel (.xlsx)" wire:model="fileImpor" accept=".xlsx,.xls">
                <x-slot:placeholder>
                    Klik atau seret file ke sini
                </x-slot:placeholder>
            </x-file>

            @error('fileImpor')
                <div class="text-red-500 text-sm">{{ $message }}</div>
            @enderror
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.imporModal = false" />
            <x-button label="Proses Impor" class="btn-primary" wire:click="impor" spinner="impor" />
        </x-slot:actions>
    </x-modal>
</div>
