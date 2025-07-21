<div>
    {{-- MODAL UNTUK MEMBUAT/MENGEDIT SOAL --}}
    <x-modal wire:model="soalModal" :title="$isEditMode ? 'Edit Soal' : 'Buat Soal Baru'" separator box-class="max-w-6xl my-16">
        <div class="flex flex-col">
            <x-form wire:submit="save" class="flex-grow overflow-y-auto pr-4 -mr-4">
                {{-- Area Pertanyaan dan Upload Gambar --}}
                <div class="p-4 mb-6 rounded-lg bg-base-200">
                    <h3 class="font-bold mb-2">1. Masukkan Pertanyaan</h3>
                    <x-textarea label="Teks Pertanyaan" wire:model="pertanyaan" rows="4"
                        placeholder="Tulis pertanyaan di sini..." />

                    {{-- Area Upload Gambar yang Lebih Baik --}}
                    <div class="mt-4">
                        <label class="label"><span class="label-text">Gambar Soal (Opsional)</span></label>
                        @if ($this->gambarSoalPreviewUrl())
                            <div class="relative group w-48 mx-auto">
                                <img src="{{ $this->gambarSoalPreviewUrl() }}"
                                    class="w-48 object-contain rounded-lg shadow-md">
                                {{-- Tombol hapus preview --}}
                                <div wire:click="removeGambarSoal"
                                    class="absolute top-0 right-0 -mt-2 -mr-2 bg-red-500 text-white rounded-full p-1.5 cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity">
                                    <x-icon name="o-x-mark" class="w-4 h-4" />
                                </div>
                            </div>
                        @else
                            {{-- Tampilkan dropzone jika tidak ada preview --}}
                            <x-file wire:model="gambar_soal" accept="image/*">
                                <x-slot:placeholder>
                                    <div class="text-center">
                                        <x-icon name="o-photo" class="w-12 h-12 mx-auto text-gray-400" />
                                        Klik atau seret gambar ke sini
                                    </div>
                                </x-slot:placeholder>
                            </x-file>
                        @endif
                    </div>
                </div>

                {{-- Area Opsi Jawaban --}}
                <div class="p-4 rounded-lg bg-base-200" x-data="{ selectedAnswer: @entangle('jawaban_benar_index').live }">
                    <h3 class="font-bold mb-2">2. Masukkan Opsi & Pilih Jawaban Benar</h3>
                    <p class="text-sm text-gray-500 mb-4">Klik pada lingkaran di sebelah kiri untuk menandai sebagai
                        jawaban
                        yang benar.</p>

                    <div class="space-y-3">

                        @foreach ($opsi as $index => $opsiItem)
                            <div wire:key="opsi-{{ $index }}">
                                <label for="opsi-radio-{{ $index }}" @class([
                                    'flex items-center gap-4 p-3 rounded-lg border-2 cursor-pointer transition',
                                ])
                                    :class="{
                                        'border-success bg-green-50 shadow-md': selectedAnswer == {{ $index }},
                                        'border-base-300 hover:border-primary': selectedAnswer != {{ $index }}
                                    }">
                                    <input id="opsi-radio-{{ $index }}" type="radio" name="jawaban_benar_radio"
                                        wire:model="jawaban_benar_index" value="{{ $index }}"
                                        class="radio radio-success" />

                                    <span class="font-bold text-lg">{{ chr(65 + (int) $index) }}</span>

                                    <div class="flex-grow">
                                        <x-input wire:model="opsi.{{ $index }}.teks"
                                            placeholder="Teks opsi {{ chr(65 + (int) $index) }}" />
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    {{-- Pesan Error yang Lebih Ramah --}}
                    @error('jawaban_benar_index')
                        <div class="text-center mt-4 p-2 bg-red-100 text-red-700 rounded-lg text-sm">
                            <x-icon name="o-exclamation-triangle" class="inline-block w-4 h-4" /> Anda harus memilih satu
                            jawaban yang benar.
                        </div>
                    @enderror
                </div>

                <x-slot:actions>
                    <x-button label="Batal" @click="$wire.soalModal = false" />
                    <x-button label="Simpan Soal" type="submit" class="btn-primary" spinner="save" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-modal>
</div>
