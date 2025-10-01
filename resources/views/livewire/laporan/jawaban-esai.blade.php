<div>
    <x-header title="Laporan Jawaban Esai" subtitle="Tinjau jawaban refleksi dan esai dari siswa." separator />

    {{-- Filter --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-8">
        <x-select label="Pilih Modul (Pulau)" :options="$this->modulOptions()" wire:model.live="modulId" placeholder="-- Pilih Modul --"
            option-value="id" option-label="judul" />
        <x-select label="Pilih Langkah Soal" :options="$this->langkahOptions()" wire:model.live="langkahId"
            placeholder="-- Pilih Soal Esai --" option-value="id" option-label="judul" :disabled="!$modulId" />
    </div>

    @if ($langkahId)
        <x-card>
            <x-input placeholder="Cari nama siswa..." wire:model.live.debounce.300ms="search" icon="o-magnifying-glass"
                class="w-full lg:w-1/3" />

            <div class="space-y-4">
                @forelse($this->jawabans() as $jawaban)
                    <div class="p-4 rounded-lg bg-base-200" wire:key="jawaban-{{ $jawaban->id }}">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <x-avatar :image="route('files.user.foto', ['userId' => $jawaban->user->id])" />
                                <div>
                                    <div class="font-bold">{{ $jawaban->user->nama }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if ($jawaban->user->kelas->first())
                                            {{ $jawaban->user->kelas->first()->nama }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-xs text-gray-400">
                                Dikirim: {{ \Carbon\Carbon::parse($jawaban->waktu_selesai)->diffForHumans() }}
                            </div>
                        </div>
                        <div class="prose max-w-none mt-4 p-3 bg-white rounded-md">
                            {!! $jawaban->jawaban_teks !!}
                        </div>
                    </div>
                @empty
                    <x-alert title="Belum ada jawaban"
                        description="Tidak ada siswa yang mengirimkan jawaban untuk soal ini." />
                @endforelse
            </div>

            <div class="mt-4">
                {{ $this->jawabans()->links() }}
            </div>
        </x-card>
    @else
        <x-alert title="Pilih Modul dan Langkah"
            description="Silakan pilih modul dan langkah soal esai untuk melihat jawaban siswa."
            icon="o-information-circle" />
    @endif
</div>
