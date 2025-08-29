<div>
    <x-header title="Manajemen Kuis 1" separator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                    class="btn-sm btn-circle btn-ghost" tooltip-left="Bantuan" />
                <x-button label="Buat Kuis 1 Baru" icon="o-plus" wire:click="create" class="btn-primary" />
            </div>
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-input placeholder="Cari judul kuis 1..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
            class="w-full lg:w-1/3" />

        @if ($this->ujians->isNotEmpty())
            <x-table :headers="$headers" :rows="$this->ujians" with-pagination class="mt-4">
                {{-- Kita akan membuat judul ujian menjadi link ke halaman detail soal --}}
                @scope('cell_judul', $ujian)
                    <a href="{{ route('ujian.soal.index', ['ujian' => $ujian->slug]) }}" class="font-bold hover:underline"
                        wire:navigate>
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
                                text: 'Menghapus kuis 1 akan menghapus semua soal dan histori terkait!',
                                next: { event: 'delete-confirmed', params: { id: '{{ $ujian->id }}' } }
                            })"
                            class="btn-sm btn-ghost text-red-500" spinner />
                    </div>
                @endscope
            </x-table>
        @else
            <x-alert title="Belum ada kuis 1 di kelas ini." icon="o-exclamation-triangle" class="mt-4" />
        @endif
    </x-card>

    {{-- MODAL UNTUK MEMBUAT/MENGEDIT UJIAN --}}
    <x-modal wire:model="ujianModal" :title="$isEditMode ? 'Edit Ujian' : 'Buat Kuis 1 Baru'" separator>
        <x-form wire:submit="save">
            <div class="space-y-4">
                <x-input label="Judul Kuis 1" wire:model="judul" />
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

    <x-modal wire:model="bantuanModal" title="Petunjuk Halaman Manajemen Ujian">
        <div class="prose max-w-none">
            <p>Halaman ini digunakan untuk mengelola "paket" atau "wadah" Ujian Pilihan Ganda untuk setiap kelas.</p>
            <ul>
                <li><strong>Pilih Kelas:</strong> Langkah pertama adalah selalu memilih kelas dari dropdown di atas.
                    Semua ujian yang Anda buat akan terikat pada kelas yang dipilih.</li>
                <li><strong>Buat Ujian Baru:</strong> Klik tombol <x-badge value="Buat Ujian Baru"
                        class="badge-primary" /> untuk membuat paket ujian. Anda akan diminta mengisi judul, waktu, dan
                    status.</li>
                <li><strong>Status Ujian:</strong>
                    <ul>
                        <li><x-badge value="Draft" class="badge-warning" />: Ujian sedang disiapkan dan **tidak bisa**
                            dilihat atau dikerjakan oleh siswa.</li>
                        <li><x-badge value="Published" class="badge-success" />: Ujian sudah siap dan **bisa**
                            dikerjakan oleh siswa.</li>
                    </ul>
                </li>
                <li><strong>Tambah/Edit Soal:</strong> Untuk mengelola soal di dalam ujian, **klik pada judul ujian** di
                    dalam tabel. Anda akan diarahkan ke halaman Manajemen Soal.</li>
            </ul>
        </div>
        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
