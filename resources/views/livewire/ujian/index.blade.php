<div>
    <x-header title="Manajemen Ujian" separator>
        <x-slot:actions>
            {{-- Tombol hanya muncul jika kelas sudah dipilih --}}
            @if ($kelasId)
                <div class="flex items-center gap-2">
                    <x-button label="Duplikasi Ujian" icon="o-document-duplicate"
                        wire:click="$dispatch('open-duplikasi-modal')" class="btn-outline" />
                    <x-button label="Buat Ujian Baru" icon="o-plus" wire:click="create" class="btn-primary" />
                </div>
            @endif
        </x-slot:actions>
    </x-header>

    {{-- AREA FILTER KELAS --}}
    <div class="mb-4">
        <x-select label="Pilih Kelas untuk Mengelola Ujian" :options="$this->kelasOptions()" wire:model.live="kelasId"
            placeholder="--- Pilih Kelas ---" icon="o-academic-cap" />
    </div>

    @if ($kelasId)
        <x-card>
            <x-input placeholder="Cari judul ujian..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                class="w-full lg:w-1/3" />

            @if ($this->ujians->isNotEmpty())
                <x-table :headers="$headers" :rows="$this->ujians" with-pagination class="mt-4">
                    {{-- Kita akan membuat judul ujian menjadi link ke halaman detail soal --}}
                    @scope('cell_judul', $ujian)
                        <a href="{{ route('ujian.soal.index', ['ujian' => $ujian->slug]) }}"
                            class="font-bold hover:underline" wire:navigate>
                            {{ $ujian->judul }}
                        </a>
                    @endscope

                    @scope('cell_status', $ujian)
                        <x-badge :value="$ujian->status" @class([
                            'badge-warning' => $ujian->status === 'Draft',
                            'badge-success' => $ujian->status === 'Published',
                        ]) />
                    @endscope

                    @scope('actions', $ujian)
                        <div class="flex items-center gap-2 justify-end" wire:key="action-{{ $ujian->id }}">
                            <x-button icon="o-pencil" wire:click="edit('{{ $ujian->id }}')" class="btn-sm btn-ghost"
                                spinner />
                            <x-button icon="o-trash"
                                wire:click.stop="$dispatch('swal:confirm', {
                                    title: 'Yakin ingin menghapus?',
                                    text: 'Menghapus ujian akan menghapus semua soal dan histori terkait!',
                                    next: { event: 'delete-confirmed', params: { id: '{{ $ujian->id }}' } }
                                })"
                                class="btn-sm btn-ghost text-red-500" spinner />
                        </div>
                    @endscope
                </x-table>
            @else
                <x-alert title="Belum ada ujian di kelas ini." icon="o-exclamation-triangle" class="mt-4" />
            @endif
        </x-card>
    @else
        <x-alert title="Silakan pilih kelas terlebih dahulu." icon="o-information-circle" />
    @endif

    {{-- MODAL UNTUK MEMBUAT/MENGEDIT UJIAN --}}
    <x-modal wire:model="ujianModal" :title="$isEditMode ? 'Edit Ujian' : 'Buat Ujian Baru'" separator>
        <x-form wire:submit="save">
            <div class="space-y-4">
                <x-input label="Judul Ujian" wire:model="judul" />
                <x-textarea label="Deskripsi" wire:model="deskripsi" hint="Opsional, instruksi singkat untuk siswa" />
                <x-input label="Waktu Pengerjaan (Menit)" wire:model="waktu_menit" type="number" />
                <x-radio label="Status" :options="[
                    ['id' => 'Draft', 'name' => 'Draft (Disimpan, belum bisa dikerjakan siswa)'],
                    ['id' => 'Published', 'name' => 'Published (Siswa bisa mengerjakan)'],
                ]" wire:model="status" />
            </div>

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.ujianModal = false" />
                <x-button label="Simpan" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    @if ($kelasId)
        <livewire:ujian.duplikasi-ujian :kelas-tujuan-id="$kelasId" wire:key="'duplikasi-ujian-modal-for-'.{{ $kelasId }}" />
    @endif
</div>
