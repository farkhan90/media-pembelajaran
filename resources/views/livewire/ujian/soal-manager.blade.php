<div>
    {{-- HEADER HALAMAN DENGAN DETAIL UJIAN --}}
    <x-header title="Manajemen Soal" subtitle="Ujian: {{ $ujian->judul }}" separator>
        {{-- SLOT BARU UNTUK TOMBOL KEMBALI --}}
        <x-slot:actions>
            <a href="{{ route('ujian.index', ['kelasId' => $ujian->kelas_id]) }}" wire:navigate>
                <x-button label="Kembali ke Daftar Kuis 1" icon="o-arrow-left" class="btn-ghost" />
            </a>
            <x-button label="Tambah Soal" icon="o-plus"
                wire:click="$dispatch('open-soal-form', { ujianId: '{{ $ujian->id }}' })" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        {{-- DAFTAR SOAL YANG SUDAH ADA --}}
        @forelse($soals as $soal)
            <div class="p-4 rounded-lg mb-4 {{ $loop->odd ? 'bg-base-200' : 'bg-base-100' }}"
                wire:key="soal-{{ $soal->id }}">
                <div class="flex justify-between items-start">
                    {{-- Pertanyaan --}}
                    <div class="prose max-w-none">
                        <p class="font-semibold">Soal #{{ $soals->firstItem() + $loop->index }}</p>
                        @if ($soal->gambar_soal)
                            <div class="mb-2 flex justify-center">
                                {{-- Gunakan rute aman yang baru --}}
                                <img src="{{ route('files.soal.gambar', ['soalId' => $soal->id]) }}"
                                    class="max-w-[200px] rounded-lg shadow-md">
                            </div>
                        @endif
                        {!! nl2br(e($soal->pertanyaan)) !!}
                    </div>
                    {{-- Aksi --}}
                    <div class="flex-shrink-0 ml-4">
                        {{-- Tombol ini sekarang dispatch event untuk edit --}}
                        <x-button icon="o-pencil"
                            wire:click="$dispatch('open-soal-form', { ujianId: '{{ $ujian->id }}', soalId: '{{ $soal->id }}' })"
                            class="btn-sm btn-ghost" spinner />
                        <x-button icon="o-trash"
                            wire:click="$dispatch('swal:confirm', { next: { event: 'delete-confirmed', params: { id: '{{ $soal->id }}' } } })"
                            class="btn-sm btn-ghost text-red-500" spinner />
                    </div>
                </div>

                {{-- Opsi Jawaban --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-4">
                    @foreach ($soal->opsiJawabans as $index => $opsi)
                        <div @class([
                            'p-2 rounded-md text-sm border',
                            'border-success bg-green-50' => $opsi->is_benar,
                            'border-base-300' => !$opsi->is_benar,
                        ])>
                            <strong>{{ chr(65 + $index) }}.</strong> {{ $opsi->teks_opsi }}
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <x-alert title="Belum ada soal untuk ujian ini." icon="o-exclamation-triangle" />
        @endforelse

        {{ $soals->links() }}
    </x-card>

    <livewire:ujian.soal-form />

</div>
