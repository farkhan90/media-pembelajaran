<div>
    <x-modal wire:model="duplikasiModal" title="Duplikasi Kuis dari Kelas Lain" persistent>
        <div class="space-y-4">
            <p>Pilih kuis dari kelas lain untuk disalin ke kelas ini. Semua pasangan item akan ikut tersalin.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-base-200 rounded-lg">
                <x-select label="Pilih Kelas Sumber" :options="$this->kelasSumberOptions()" wire:model.live="kelasSumberId"
                    placeholder="Pilih sebuah kelas" />
                <x-select label="Pilih Kuis yang Akan Diduplikasi" :options="$this->kuisSumberOptions()" wire:model.live="kuisSumberId"
                    placeholder="Pilih sebuah kuis" option-value="id" option-label="judul" :disabled="!$kelasSumberId" />
            </div>

            @if ($kuisSumberId)
                @php
                    $kuisDipilih = \App\Models\KuisMenjodohkan::find($kuisSumberId);
                @endphp
                @if ($kuisDipilih)
                    <x-alert title="Konfirmasi" icon="o-exclamation-triangle" class="alert-info">
                        Anda akan membuat salinan dari kuis **"{{ $kuisDipilih->judul }}"** ke dalam kelas ini.
                        Lanjutkan?
                    </x-alert>
                @endif
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.duplikasiModal = false" />
            <x-button label="Ya, Duplikasi Kuis Ini" class="btn-primary" wire:click="prosesDuplikasi"
                spinner="prosesDuplikasi" :disabled="!$kuisSumberId" />
        </x-slot:actions>
    </x-modal>
</div>
