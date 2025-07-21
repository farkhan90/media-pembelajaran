<div>
    <x-header title="Manajemen Item Kuis" {{-- Panggil sebagai metode dan akses properti 'judul' --}} subtitle="Kuis: {{ $this->kuis()->judul }}" separator>
        <x-slot:actions>
            <a href="{{ route('kuis.index', ['kelasId' => $this->kuis()->kelas_id]) }}" wire:navigate>
                <x-button label="Kembali ke Daftar Kuis" icon="o-arrow-left" class="btn-ghost" />
            </a>
            <x-button label="Tambah Pasangan Item" icon="o-plus" wire:click="create" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        @forelse($items as $item)
            <div class="grid grid-cols-12 gap-4 items-center p-4 rounded-lg mb-4 {{ $loop->odd ? 'bg-base-200' : 'bg-base-100' }}"
                wire:key="item-{{ $item->id }}">
                {{-- Kolom Pertanyaan (Kiri) --}}
                <div class="col-span-5">
                    @if ($item->tipe_item === 'Teks')
                        <p>{{ $item->konten }}</p>
                    @else
                        <img src="{{ route('kuis.item-pertanyaan.gambar', ['itemPertanyaanId' => $item->id]) }}"
                            class="max-w-[150px] max-h-[150px] object-contain rounded-lg">
                    @endif
                </div>
                {{-- Panah --}}
                <div class="col-span-2 text-center">
                    <x-icon name="o-arrows-right-left" class="w-8 h-8 text-primary" />
                </div>
                {{-- Kolom Jawaban (Kanan) --}}
                <div class="col-span-5">
                    @if ($item->itemJawaban->tipe_item === 'Teks')
                        <p class="font-semibold">{{ $item->itemJawaban->konten }}</p>
                    @else
                        <img src="{{ route('kuis.item-jawaban.gambar', ['itemJawabanId' => $item->itemJawaban->id]) }}"
                            class="max-w-[150px] max-h-[150px] object-contain rounded-lg">
                    @endif
                </div>
                {{-- Aksi --}}
                <div class="col-span-12 md:col-span-2 md:col-start-11 flex justify-end">
                    <x-button icon="o-pencil" wire:click="edit('{{ $item->id }}')" class="btn-sm btn-ghost"
                        spinner />
                    <x-button icon="o-trash"
                        wire:click="$dispatch('swal:confirm', { next: { event: 'delete-confirmed', params: { id: '{{ $item->id }}' } } })"
                        class="btn-sm btn-ghost text-red-500" spinner />
                </div>
            </div>
        @empty
            <x-alert title="Belum ada pasangan item untuk kuis ini." icon="o-exclamation-triangle" />
        @endforelse

        {{ $items->links() }}
    </x-card>

    {{-- MODAL UNTUK ITEM --}}
    <x-modal wire:model="itemModal" :title="$isEditMode ? 'Edit Pasangan Item' : 'Tambah Pasangan Item Baru'" separator>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- KOLOM KIRI: PERTANYAAN --}}
                <div class="space-y-4 p-4 rounded-lg bg-base-200">
                    <h3 class="font-bold">Item Pertanyaan (Kiri)</h3>
                    <x-radio :options="[['id' => 'Teks', 'name' => 'Teks'], ['id' => 'Gambar', 'name' => 'Gambar']]" wire:model.live="pertanyaan_tipe" />
                    @if ($pertanyaan_tipe === 'Teks')
                        <x-textarea label="Konten Teks" wire:model="pertanyaan_konten" />
                    @else
                        @if ($this->gambarPertanyaanPreviewUrl())
                            <div class="relative group w-40 mx-auto">
                                <img src="{{ $this->gambarPertanyaanPreviewUrl() }}"
                                    class="w-40 object-contain rounded-lg shadow-md">
                                <div wire:click="removeGambar('pertanyaan')"
                                    class="absolute top-0 right-0 -mt-2 -mr-2 ... cursor-pointer ...">
                                    <x-icon name="o-x-mark" class="w-4 h-4" />
                                </div>
                            </div>
                        @else
                            <x-file label="File Gambar" wire:model="pertanyaan_gambar" accept="image/*" />
                        @endif
                    @endif
                </div>
                {{-- KOLOM KANAN: JAWABAN --}}
                <div class="space-y-4 p-4 rounded-lg bg-base-200">
                    <h3 class="font-bold">Item Jawaban (Kanan)</h3>
                    <x-radio :options="[['id' => 'Teks', 'name' => 'Teks'], ['id' => 'Gambar', 'name' => 'Gambar']]" wire:model.live="jawaban_tipe" />
                    @if ($jawaban_tipe === 'Teks')
                        <x-textarea label="Konten Teks" wire:model="jawaban_konten" />
                    @else
                        @if ($this->gambarJawabanPreviewUrl())
                            <div class="relative group w-40 mx-auto">
                                <img src="{{ $this->gambarJawabanPreviewUrl() }}"
                                    class="w-40 object-contain rounded-lg shadow-md">
                                <div wire:click="removeGambar('jawaban')"
                                    class="absolute top-0 right-0 -mt-2 -mr-2 ... cursor-pointer ...">
                                    <x-icon name="o-x-mark" class="w-4 h-4" />
                                </div>
                            </div>
                        @else
                            <x-file label="File Gambar" wire:model="jawaban_gambar" accept="image/*" />
                        @endif
                    @endif
                </div>
            </div>
            <x-slot:actions>
                <x-button label="Batal" @click="$itemModal = false" />
                <x-button label="Simpan Pasangan" type="submit" class="btn-primary" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
