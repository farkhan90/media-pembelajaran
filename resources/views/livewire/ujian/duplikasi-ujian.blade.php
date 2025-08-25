<div>
    <x-modal wire:model="duplikasiModal" title="Duplikasi Kuis 1 dari Kelas Lain" persistent>
        <div class="space-y-4">
            <p>Pilih Kuis 1 dari kelas lain untuk disalin ke kelas ini. Semua soal dan opsi jawaban akan ikut tersalin.
            </p>

            {{-- Filter --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-base-200 rounded-lg">
                <x-select label="Pilih Kelas Sumber" :options="$this->kelasSumberOptions()" wire:model.live="kelasSumberId"
                    placeholder="Pilih sebuah kelas" />
                <x-select label="Pilih Kuis 1 yang Akan Diduplikasi" :options="$this->ujianSumberOptions" wire:model.live="ujianSumberId"
                    placeholder="Pilih sebuah Kuis" option-value="id" option-label="judul" :disabled="!$kelasSumberId" />
            </div>

            @if ($ujianSumberId)
                <x-alert title="Konfirmasi" icon="o-exclamation-triangle" class="alert-info">
                    Anda akan membuat salinan dari Kuis **"{{ \App\Models\Ujian::find($ujianSumberId)->judul }}"** ke
                    dalam kelas ini. Lanjutkan?
                </x-alert>
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.duplikasiModal = false" />
            <x-button label="Ya, Duplikasi Kuis 1 Ini" class="btn-primary" wire:click="prosesDuplikasi"
                spinner="prosesDuplikasi" :disabled="!$ujianSumberId" />
        </x-slot:actions>
    </x-modal>
</div>
