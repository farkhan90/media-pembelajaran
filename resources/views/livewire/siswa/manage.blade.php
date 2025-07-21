<div>
    <x-header title="Manajemen Siswa per Kelas" separator>
        <x-slot:actions>
            @if ($kelasId)
                <div class="flex items-center gap-2">
                    <x-button label="Atur Siswa" icon="o-arrows-right-left" wire:click="openTransferModal"
                        class="btn-outline" />

                    {{-- UBAH TOMBOL INI UNTUK MEMBUKA MODAL BARU --}}
                    <x-button label="Tambah Siswa Baru" icon="o-plus" wire:click="openCreateSiswaModal"
                        class="btn-primary" />
                </div>
            @endif
        </x-slot:actions>
    </x-header>

    {{-- AREA FILTER --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <x-select label="Pilih Sekolah" :options="$this->sekolahOptions" wire:model.live="sekolahId"
            placeholder="--- Semua Sekolah ---" option-value="id" option-label="nama" />
        <x-select label="Pilih Kelas" :options="$this->kelasOptions" wire:model.live="kelasId" placeholder="--- Pilih Kelas ---"
            option-value="id" option-label="nama" :disabled="!$sekolahId" />
    </div>

    {{-- JIKA KELAS SUDAH DIPILIH, TAMPILKAN TABEL --}}
    @if ($kelasId)
        <x-card>
            <x-input placeholder="Cari siswa di kelas ini..." wire:model.live.debounce.300ms="search"
                icon="o-magnifying-glass" class="w-full lg:w-1/3" />

            @if ($this->siswas->isNotEmpty())
                <x-table :headers="$headers" :rows="$this->siswas" with-pagination class="mt-3">
                    @scope('cell_foto', $siswa)
                        @if ($siswa->foto)
                            <x-avatar :image="route('files.user.foto', ['userId' => $siswa->id])" class="!w-14 !h-14" />
                        @else
                            <div class="flex items-center justify-center h-14 w-14 bg-gray-200 rounded-md">
                                <x-icon name="o-photo" class="text-gray-400" />
                            </div>
                        @endif
                    @endscope
                </x-table>
            @else
                <x-alert title="Tidak ada siswa di kelas ini." icon="o-exclamation-triangle"
                    class="alert-warning mt-3" />
            @endif
        </x-card>
    @else
        <x-alert title="Silakan pilih sekolah dan kelas terlebih dahulu untuk melihat daftar siswa."
            icon="o-information-circle" />
    @endif

    {{-- MODAL UNTUK TRANSFER SISWA --}}
    <x-modal wire:model="transferModal" title="Atur Siswa di Kelas" persistent
        box-class="max-w-6xl my-16 max-h-none md:my-16">
        <p class="mb-4">Pilih siswa dari kolom "Siswa Tersedia" untuk ditambahkan, atau pilih siswa dari kolom "Siswa
            di Kelas Ini" untuk dikeluarkan.</p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-4 rounded-lg bg-base-200">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <x-icon name="o-users" class="w-6 h-6 text-info" />
                    <h3 class="font-bold text-lg">Siswa Tersedia</h3>
                    @if (count($siswaTanpaKelasIds) > 0)
                        <x-badge :value="count($siswaTanpaKelasIds) . ' dipilih'" class="badge-info badge-outline" />
                    @endif
                </div>

                <div class="h-96 overflow-y-auto rounded-lg p-2 space-y-1 bg-base-100 shadow-inner">
                    @forelse($this->siswaBelumPunyaKelas as $siswa)
                        <div x-data @@click="$refs.checkbox.click()" @class([
                            'flex items-center p-2 rounded-lg cursor-pointer transition-colors',
                            'hover:bg-blue-50' => !in_array($siswa->id, $siswaTanpaKelasIds),
                            'bg-blue-100 border border-blue-300' => in_array(
                                $siswa->id,
                                $siswaTanpaKelasIds),
                        ])>
                            @if ($siswa->foto)
                                <x-avatar :image="route('files.user.foto', ['userId' => $siswa->id])" class="!w-14 !h-14 mr-4" />
                            @else
                                <div class="flex items-center justify-center h-14 w-14 bg-gray-200 rounded-md mr-4">
                                    <x-icon name="o-photo" class="text-gray-400" />
                                </div>
                            @endif
                            <div class="flex-grow">
                                <div class="font-semibold">{{ $siswa->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $siswa->email }}</div>
                            </div>
                            <x-checkbox x-ref="checkbox" wire:model.live="siswaTanpaKelasIds"
                                value="{{ $siswa->id }}" class="checkbox-info" />
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-gray-400">
                            <x-icon name="o-check-circle" class="w-16 h-16" />
                            <p class="mt-2">Semua siswa sudah memiliki kelas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <x-icon name="o-user-minus" class="w-6 h-6 text-warning" />
                    <h3 class="font-bold text-lg">Siswa di Kelas Ini (Untuk Dikeluarkan)</h3>
                    @if (count($siswaDiKelasIds) > 0)
                        <x-badge :value="count($siswaDiKelasIds) . ' dipilih'" class="badge-warning badge-outline" />
                    @endif
                </div>

                <div class="h-96 overflow-y-auto rounded-lg p-2 space-y-1 bg-base-100 shadow-inner">
                    @forelse($this->siswas as $siswa)
                        <div x-data @@click="$refs.checkbox.click()" @class([
                            'flex items-center p-2 rounded-lg cursor-pointer transition-colors',
                            'hover:bg-red-50' => !in_array($siswa->id, $siswaDiKelasIds),
                            'bg-red-100 border border-red-300' => in_array(
                                $siswa->id,
                                $siswaDiKelasIds),
                        ])>
                            @if ($siswa->foto)
                                <x-avatar :image="route('files.user.foto', ['userId' => $siswa->id])" class="!w-14 !h-14 mr-4" />
                            @else
                                <div class="flex items-center justify-center h-14 w-14 bg-gray-200 rounded-md mr-4">
                                    <x-icon name="o-photo" class="text-gray-400" />
                                </div>
                            @endif
                            <div class="flex-grow">
                                <div class="font-semibold">{{ $siswa->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $siswa->email }}</div>
                            </div>
                            <x-checkbox x-ref="checkbox" wire:model.live="siswaDiKelasIds" value="{{ $siswa->id }}"
                                class="checkbox-warning" />
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-gray-400">
                            <x-icon name="o-x-circle" class="w-16 h-16" />
                            <p class="mt-2">Belum ada siswa di kelas ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.transferModal = false" />
            <x-button label="Simpan Perubahan" class="btn-primary" wire:click="transferSiswa" spinner />
        </x-slot:actions>
    </x-modal>

    {{-- MODAL UNTUK MEMBUAT SISWA BARU --}}
    <x-modal wire:model="createSiswaModal" title="Tambah Siswa Baru ke Kelas" separator>
        <x-form wire:submit="saveSiswa">
            <div class="space-y-4">
                <x-file label="Foto Profil" wire:model="foto" accept="image/png, image/jpeg" hint="Opsional" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input label="Nama Lengkap" wire:model="nama" />
                    <x-input label="Email" wire:model="email" type="email" />

                    <div class="md:col-span-2">
                        <x-radio label="Jenis Kelamin" :options="[['id' => 'L', 'name' => 'Laki-laki'], ['id' => 'P', 'name' => 'Perempuan']]" wire:model="jk" />
                    </div>

                    <x-input label="Password" wire:model="password" type="password" />
                    <x-input label="Konfirmasi Password" wire:model="password_confirmation" type="password" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.createSiswaModal = false" />
                <x-button label="Simpan & Tambah ke Kelas" type="submit" class="btn-primary" spinner="saveSiswa" />
            </x-slot:actions>
        </x-form>
    </x-modal>

</div>
