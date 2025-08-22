<div>
    <x-header title="Manajemen Kuis Menjodohkan" separator>
        <x-slot:actions>
            @if ($kelasId)
                <x-button label="Duplikasi Kuis" icon="o-document-duplicate"
                    wire:click="$dispatch('open-duplikasi-kuis-modal')" class="btn-outline" />
                <x-button label="Buat Kuis Baru" icon="o-plus" wire:click="create" class="btn-primary" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- AREA FILTER KELAS --}}
    <div class="mb-4">
        <x-select label="Pilih Kelas untuk Mengelola Kuis" :options="$this->kelasOptions()" wire:model.live="kelasId"
            placeholder="--- Pilih Kelas ---" icon="o-academic-cap" />
    </div>

    @if ($kelasId)
        <x-card>
            <x-input placeholder="Cari judul kuis..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                class="w-full lg:w-1/3" />

            <x-table :headers="$headers" :rows="$this->kuises" with-pagination>
                @scope('cell_judul', $kuis)
                    <a href="{{ route('kuis.items.index', ['kuisMenjodohkan' => $kuis->id]) }}"
                        class="font-bold hover:underline" wire:navigate>
                        {{ $kuis->judul }}
                    </a>
                @endscope

                @scope('cell_status', $kuis)
                    <x-badge :value="$kuis->status" @class([
                        'badge-warning' => $kuis->status === 'Draft',
                        'badge-success' => $kuis->status === 'Published',
                    ]) />
                @endscope

                @scope('actions', $kuis)
                    <div class="flex items-center gap-2 justify-end" wire:key="action-{{ $kuis->id }}">
                        <x-button icon="o-pencil" wire:click="edit('{{ $kuis->id }}')" class="btn-sm btn-ghost"
                            spinner />
                        <x-button icon="o-trash"
                            wire:click="$dispatch('swal:confirm', {
                                title: 'Yakin ingin menghapus?',
                                text: 'Menghapus kuis akan menghapus semua pasangan item di dalamnya!',
                                next: { event: 'delete-confirmed', params: { id: '{{ $kuis->id }}' } }
                            })"
                            class="btn-sm btn-ghost text-red-500" spinner />
                    </div>
                @endscope
            </x-table>
        </x-card>
    @else
        <x-alert title="Silakan pilih kelas terlebih dahulu." icon="o-information-circle" />
    @endif

    {{-- MODAL UNTUK MEMBUAT/MENGEDIT KUIS --}}
    <x-modal wire:model="kuisModal" :title="$isEditMode ? 'Edit Kuis' : 'Buat Kuis Baru'" separator>
        <x-form wire:submit="save">
            <div class="space-y-4">
                <x-input label="Judul Kuis" wire:model="judul" />
                <x-textarea label="Deskripsi" wire:model="deskripsi" hint="Opsional, instruksi singkat untuk siswa" />
                <x-radio label="Status" :options="[
                    ['id' => 'Draft', 'name' => 'Draft (Disimpan, belum bisa dikerjakan)'],
                    ['id' => 'Published', 'name' => 'Published (Siswa bisa mengerjakan)'],
                ]" wire:model="status" />
            </div>

            <x-slot:actions>
                <x-button label="Batal" @click="$wire.kuisModal = false" />
                <x-button label="Simpan" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    @if ($kelasId)
        <livewire:kuis-menjodohkan.duplikasi-kuis :kelas-tujuan-id="$kelasId"
            wire:key="'duplikasi-kuis-modal-for-'.{{ $kelasId }}" />
    @endif
</div>
