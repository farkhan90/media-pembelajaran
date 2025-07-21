<div>
    <x-modal wire:model="duplikasiModal" title="Duplikasi Ujian dari Kelas Lain" persistent>
        <div class="space-y-4">
            <p>Pilih ujian dari kelas lain untuk disalin ke kelas ini. Semua soal dan opsi jawaban akan ikut tersalin.
            </p>

            {{-- Filter --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-base-200 rounded-lg">
                <x-select label="Pilih Kelas Sumber" :options="$this->kelasSumberOptions()" wire:model.live="kelasSumberId"
                    placeholder="Pilih sebuah kelas" />
                <x-select label="Pilih Ujian yang Akan Diduplikasi" :options="$this->ujianSumberOptions" wire:model.live="ujianSumberId"
                    placeholder="Pilih sebuah ujian" option-value="id" option-label="judul" :disabled="!$kelasSumberId" />
            </div>

            @if ($ujianSumberId)
                <x-alert title="Konfirmasi" icon="o-exclamation-triangle" class="alert-info">
                    Anda akan membuat salinan dari ujian **"{{ \App\Models\Ujian::find($ujianSumberId)->judul }}"** ke
                    dalam kelas ini. Lanjutkan?
                </x-alert>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.duplikasiModal = false" />
            <x-button label="Ya, Duplikasi Ujian Ini" class="btn-primary" wire:click="prosesDuplikasi"
                spinner="prosesDuplikasi" :disabled="!$ujianSumberId" />
        </x-slot:actions>
    </x-modal>
</div>
