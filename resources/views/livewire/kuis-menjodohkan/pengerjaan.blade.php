<div class="relative w-full min-h-screen p-4 font-sans bg-cover bg-center"
    style="background-image: url('{{ asset('assets/img/backgrounds/quiz-bg.jpg') }}');">
    <div class="absolute inset-0 w-full h-full pointer-events-none overflow-hidden" x-data x-init="// Animasikan semua elemen dengan kelas 'clipart'
    gsap.from('.clipart', {
        scale: 0,
        opacity: 0,
        rotation: () => gsap.utils.random(-180, 180),
        stagger: 0.1,
        duration: 1,
        ease: 'back.out(1.7)',
        delay: 0.5
    })">
        {{-- Tempatkan clipart di posisi acak dengan rotasi --}}
        <img src="{{ asset('assets/img/cliparts/pencil.svg') }}" alt="Clipart Pensil"
            class="clipart absolute w-32 -rotate-12" style="top: 10%; left: 5%;">
        <img src="{{ asset('assets/img/cliparts/eraser.svg') }}" alt="Clipart Penghapus"
            class="clipart absolute w-20 rotate-12" style="top: 80%; left: 15%;">
        <img src="{{ asset('assets/img/cliparts/ruler.svg') }}" alt="Clipart Penggaris"
            class="clipart absolute w-40 rotate-[25deg]" style="top: 15%; right: 10%;">
        <img src="{{ asset('assets/img/cliparts/book.svg') }}" alt="Clipart Buku"
            class="clipart absolute w-28 -rotate-[15deg]" style="bottom: 8%; right: 5%;">
        <img src="{{ asset('assets/img/cliparts/paperclip.svg') }}" alt="Clipart Penjepit Kertas"
            class="clipart absolute w-16 rotate-45" style="top: 50%; left: 20%;">
    </div>

    <x-header :title="$kuis->judul" separator class="mb-8 text-white" />
    <div class="max-w-3xl mx-auto mb-8 flex items-center gap-4" x-data x-init="gsap.from($el, { y: -50, opacity: 0, duration: 1, ease: 'elastic.out(1, 0.5)', delay: 0.3 })">
        {{-- Ikon Karakter / Maskot --}}
        <div class="flex-shrink-0">
            {{-- Anda bisa mengganti ikon ini dengan gambar maskot jika punya --}}
            <x-icon name="o-light-bulb" class="w-20 h-20 text-yellow-400 drop-shadow-lg" />
        </div>

        {{-- Gelembung Ucapan (Speech Bubble) --}}
        <div class="relative bg-white p-6 rounded-2xl shadow-lg text-gray-700 text-lg font-semibold">
            {{-- Segitiga kecil untuk menunjuk ke ikon --}}
            <div
                class="absolute -left-4 top-1/2 -translate-y-1/2 w-0 h-0 
                    border-t-[15px] border-t-transparent
                    border-r-[20px] border-r-white
                    border-b-[15px] border-b-transparent">
            </div>

            Ayo, Petualang Cerdas! <br>
            Bacalah kata di sebelah <span class="text-blue-500 font-bold">kiri</span>, lalu cari pasangannya yang tepat
            di sebelah <span class="text-pink-500 font-bold">kanan</span>. Klik untuk menghubungkan!
        </div>
    </div>

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
    <div class="mt-12 flex justify-center items-center gap-4 text-white">
        <x-button label="Reset Jawaban" icon="o-arrow-path" class="btn-ghost" wire:click="resetSemuaJawaban" spinner />
        <x-button label="Selesaikan Kuis" class="btn-primary btn-lg" wire:click="selesaikanKuis"
            spinner="selesaikanKuis" :disabled="count($pasanganSiswa) < $itemPertanyaans->count()" />
    </div>
</div>
