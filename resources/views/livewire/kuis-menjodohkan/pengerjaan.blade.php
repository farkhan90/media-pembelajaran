<div class="bg-gray-100 min-h-screen p-4 md:p-8 font-sans">

    <x-header :title="$kuis->judul" :subtitle="$kuis->deskripsi" separator class="mb-8" />

    <div x-data="{
        pasangan: @entangle('pasanganSiswa'),
        selectedPertanyaan: @entangle('selectedPertanyaanId'),
        warnaGaris: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6'],
    
        // Fungsi utama sekarang akan memanipulasi DOM secara langsung
        gambarGaris() {
            const svgContainer = this.$refs.svgContainer;
            if (!svgContainer) return;
    
            // 1. Hapus semua garis lama
            svgContainer.innerHTML = '';
            this.resetAllBorders();
    
            this.$nextTick(() => {
                let warnaIndex = 0;
                const container = this.$refs.container;
                if (!container) return;
                const containerRect = container.getBoundingClientRect();
    
                for (const pertanyaanId in this.pasangan) {
                    const jawabanId = this.pasangan[pertanyaanId];
                    const elPertanyaan = this.$refs['pertanyaan-' + pertanyaanId];
                    const elJawaban = this.$refs['jawaban-' + jawabanId];
    
                    if (elPertanyaan && elJawaban) {
                        const rectP = elPertanyaan.getBoundingClientRect();
                        const rectJ = elJawaban.getBoundingClientRect();
                        const warna = this.warnaGaris[warnaIndex % this.warnaGaris.length];
                        warnaIndex++;
    
                        const penyesuaian = 3;
                        const geserKiri = 16;
    
                        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    
                        // Ubah baris ini: Kurangi sedikit agar masuk ke dalam kotak kiri
                        line.setAttribute('x1', (rectP.right - containerRect.left) - geserKiri);
    
                        line.setAttribute('y1', rectP.top + rectP.height / 2 - containerRect.top);
    
                        // Ubah baris ini: Tambah sedikit agar masuk ke dalam kotak kanan
                        line.setAttribute('x2', (rectJ.left - containerRect.left) + penyesuaian);
    
                        line.setAttribute('y2', rectJ.top + rectJ.height / 2 - containerRect.top);
    
                        // =======================================================
    
                        line.setAttribute('stroke', warna);
                        line.setAttribute('stroke-width', '3');
                        line.setAttribute('stroke-linecap', 'round');
    
                        svgContainer.appendChild(line);
    
                        elPertanyaan.style.borderColor = warna;
                        elJawaban.style.borderColor = warna;
                    }
                }
            });
        },
    
        resetAllBorders() {
            for (const key in this.$refs) {
                if (key.startsWith('pertanyaan-') || key.startsWith('jawaban-')) {
                    if (this.$refs[key]) this.$refs[key].style.borderColor = 'transparent';
                }
            }
        }
    }" x-init="gambarGaris();
    $watch('pasangan', () => gambarGaris());" @@resize.window.debounce.150ms="gambarGaris()"
        class="relative max-w-6xl mx-auto" x-ref="container">
        <svg class="absolute top-0 left-0 w-full h-full pointer-events-none z-0">
            {{-- Beri 'x-ref' agar kita bisa menargetkannya dari JavaScript --}}
            <g x-ref="svgContainer"></g>
        </svg>

        <div class="grid grid-cols-2 gap-12 md:gap-48 relative z-10">
            {{-- KOLOM KIRI (PERTANYAAN) --}}
            <div class="space-y-6">
                @foreach ($itemPertanyaans as $item)
                    <div x-ref="pertanyaan-{{ $item->id }}" wire:click="pilihPertanyaan('{{ $item->id }}')"
                        class="p-4 bg-white border-4 rounded-2xl cursor-pointer transition-all duration-300 transform shadow-md hover:shadow-xl hover:scale-102"
                        :class="{
                            'border-blue-500 scale-105 shadow-2xl': selectedPertanyaan == '{{ $item->id }}',
                            'border-transparent': selectedPertanyaan != '{{ $item->id }}'
                        }">
                        @if ($item->tipe_item === 'Teks')
                            <span
                                class="text-lg md:text-xl font-semibold text-gray-800 block text-center">{{ $item->konten }}</span>
                        @else
                            <img src="{{ route('kuis.item-pertanyaan.gambar', ['itemPertanyaanId' => $item->id]) }}"
                                class="max-w-[150px] max-h-[150px] object-contain rounded-lg">
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- KOLOM KANAN (JAWABAN) --}}
            <div class="space-y-6">
                @foreach ($itemJawabans as $item)
                    <div x-ref="jawaban-{{ $item->id }}" wire:click="pilihJawaban('{{ $item->id }}')"
                        class="p-4 bg-white border-4 border-transparent rounded-2xl transition-all duration-300 transform shadow-md"
                        :class="{
                            'opacity-50 !cursor-not-allowed': {{ in_array($item->id, $pasanganSiswa) ? 'true' : 'false' }},
                            'cursor-pointer hover:shadow-xl hover:scale-102': {{ !in_array($item->id, $pasanganSiswa) ? 'true' : 'false' }}
                        }">
                        @if ($item->tipe_item === 'Teks')
                            <span
                                class="text-lg md:text-xl font-semibold text-gray-800 block text-center">{{ $item->konten }}</span>
                        @else
                            <img src="{{ route('kuis.item-jawaban.gambar', ['itemJawabanId' => $item->itemJawaban->id]) }}"
                                class="max-w-[150px] max-h-[150px] object-contain rounded-lg">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TOMBOL AKSI --}}
    <div class="mt-12 flex justify-center items-center gap-4">
        <x-button label="Reset Jawaban" icon="o-arrow-path" class="btn-ghost" wire:click="resetSemuaJawaban" spinner />
        <x-button label="Selesaikan Kuis" class="btn-primary btn-lg" wire:click="selesaikanKuis"
            spinner="selesaikanKuis" :disabled="count($pasanganSiswa) < $itemPertanyaans->count()" />
    </div>
</div>
