<div class="w-full min-h-screen p-4 md:p-8 font-sans bg-gradient-to-br from-yellow-100 via-orange-100 to-red-100"
    {{-- Jika ingin pakai gambar: --}} {{-- style="background-image: url('{{ asset('assets/img/backgrounds/confetti-bg.jpg') }}');" --}}>
    {{-- Kontainer Utama --}}
    <div class="max-w-5xl mx-auto" x-data x-init="gsap.from($el, { y: 50, opacity: 0, duration: 1, ease: 'power2.out' })">
        {{-- HEADER HALAMAN --}}
        <div class="text-center mb-10">
            <x-icon name="o-trophy" class="w-20 h-20 mx-auto text-yellow-500 drop-shadow-lg" />
            <h1 class="text-4xl md:text-5xl font-lilita text-gray-800 mt-4"
                style="-webkit-text-stroke: 1px #FBBF24; text-stroke: 1px #FBBF24;">
                Papan Peringkat Petualangan
            </h1>
            <p class="text-gray-600 text-lg mt-2">Pulau Papua - Penilaian Akhir</p>
            <div class="mt-6">
                <a href="{{ route('peta-petualangan') }}" wire:navigate>
                    <x-button label="Kembali ke Peta" icon="o-arrow-left" class="btn-ghost" />
                </a>
            </div>
        </div>

        @if ($this->podiumSiswa->isNotEmpty())
            {{-- PODIUM UNTUK 3 PERINGKAT TERATAS --}}
            <div class="grid grid-cols-3 gap-4 md:gap-8 mb-10 text-center" x-data x-init="gsap.from($el.children, { y: 50, opacity: 0, scale: 0.8, stagger: 0.2, duration: 0.8, ease: 'back.out(1.7)', delay: 0.5 })">
                {{-- Peringkat 2 (Perak) --}}
                @if ($this->podiumSiswa->has(1))
                    <div class="mt-8 flex flex-col items-center">
                        <x-icon name="s-academic-cap" class="w-16 h-16 text-gray-400" />
                        <x-avatar :image="route('files.user.foto', ['userId' => $podiumSiswa[1]->user->id])" class="!w-24 !h-24 my-2 ring-4 ring-gray-400" />
                        <h3 class="font-bold text-lg">{{ $podiumSiswa[1]->user->nama }}</h3>
                        <p class="font-bold text-2xl text-gray-600">{{ round($podiumSiswa[1]->skor_akumulasi, 2) }}</p>
                        <div class="mt-2 text-2xl font-lilita text-gray-500">#2</div>
                    </div>
                @else
                    <div></div> {{-- Placeholder jika tidak ada peringkat 2 --}}
                @endif

                {{-- Peringkat 1 (Emas) --}}
                @if ($this->podiumSiswa->has(0))
                    <div class="flex flex-col items-center"> {{-- Tambah flexbox di sini --}}
                        <x-icon name="s-trophy" class="w-20 h-20 text-yellow-500" />
                        <x-avatar :image="route('files.user.foto', ['userId' => $podiumSiswa[0]->user->id])" class="!w-32 !h-32 my-2 ring-4 ring-yellow-500" />
                        <h3 class="font-bold text-xl">{{ $podiumSiswa[0]->user->nama }}</h3>
                        <p class="font-bold text-3xl text-yellow-600">{{ round($podiumSiswa[0]->skor_akumulasi, 2) }}
                        </p>
                        <div class="mt-2 text-3xl font-lilita text-yellow-500">#1</div>
                    </div>
                @endif

                {{-- Peringkat 3 (Perunggu) --}}
                @if ($this->podiumSiswa->has(2))
                    <div class="mt-8 flex flex-col items-center"> {{-- Tambah flexbox di sini --}}
                        <x-icon name="s-academic-cap" class="w-16 h-16 text-yellow-700" />
                        <x-avatar :image="route('files.user.foto', ['userId' => $podiumSiswa[2]->user->id])" class="!w-24 !h-24 my-2 ring-4 ring-yellow-700" />
                        <h3 class="font-bold text-lg">{{ $podiumSiswa[2]->user->nama }}</h3>
                        <p class="font-bold text-2xl text-yellow-800">{{ round($podiumSiswa[2]->skor_akumulasi, 2) }}
                        </p>
                        <div class="mt-2 text-2xl font-lilita text-yellow-600">#3</div>
                    </div>
                @else
                    <div></div> {{-- Placeholder jika tidak ada peringkat 3 --}}
                @endif
            </div>

            {{-- TABEL UNTUK PERINGKAT SELANJUTNYA --}}
            <h3 class="text-xl font-bold text-center text-gray-700 mb-4">Peringkat Lainnya</h3>
            <div class="bg-white rounded-xl shadow-lg">
                <x-table :headers="$headers" :rows="$peringkatTabel" with-pagination>

                    @scope('cell_peringkat', $progres)
                        <div class="font-bold text-center">
                            {{-- Gunakan variabel view $peringkatTabel, bukan $this->peringkatTabel --}}
                            {{ $peringkatTabel->firstItem() + $loop->index }}
                        </div>
                    @endscope
                    @scope('cell_user.nama', $progres)
                        {{ $progres->user->nama }}
                    @endscope

                    {{-- ... sisa scope ... --}}

                </x-table>
            </div>
        @else
            <x-alert title="Belum ada siswa yang menyelesaikan penilaian ini." icon="o-information-circle"
                class="mt-8" />
        @endif
    </div>
</div>
