<div class="relative w-full h-screen overflow-hidden bg-cover bg-center font-sans"
    style="background-image: url('{{ asset('assets/img/backgrounds/modul-bg.png') }}');">
    <div class="relative z-10 h-full flex flex-col">
        <header class="bg-white shadow-md p-4 flex-shrink-0 z-10">
            <div class="max-w-5xl mx-auto"> {{-- Batasi lebar header --}}
                {{-- Baris Atas: Judul & Tombol Kembali --}}
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Petualangan di Pulau {{ Str::ucfirst($modul->nama_pulau) }}</p>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $modul->judul }}</h1>
                    </div>
                    <a href="{{ route('peta-petualangan') }}" wire:navigate class="btn btn-ghost">
                        <x-icon name="o-map" class="w-5 h-5" />
                        Kembali ke Peta
                    </a>
                </div>

                {{-- ======================================================= --}}
                {{--      PROGRESS STEPPER / TIMELINE YANG DIPERBARUI        --}}
                {{-- ======================================================= --}}
                <div>
                    {{-- Progress Bar di Atas --}}
                    <div class="flex justify-between items-center text-sm text-gray-600 mb-1">
                        <span>Progres Modul</span>
                        <span class="font-bold">{{ $this->progresPersen() }}%</span>
                    </div>
                    <x-progress :value="$this->progresPersen()" class="progress-primary h-2" />

                    {{-- Stepper Ikon --}}
                    <div class="flex items-center justify-between mt-2">
                        @foreach ($modul->langkahs as $index => $langkah)
                            @php
                                $isSelesai = $langkahSelesaiIds->contains($langkah->id);
                                $isAktif = $langkahAktif && $langkahAktif->id === $langkah->id;
                                $isGuruAdmin = in_array(auth()->user()->role, ['Admin', 'Guru']);
                                $isClickable = $isSelesai || $isGuruAdmin;
                            @endphp

                            {{-- Lingkaran Status Ikon --}}
                            <div @if ($isClickable) wire:click="goToLangkah('{{ $langkah->id }}')" @endif
                                @class([
                                    'w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-all transform',
                                    'bg-primary text-white ring-4 ring-primary/30 scale-110' => $isAktif,
                                    'bg-success text-white' => $isSelesai && !$isAktif,
                                    'bg-gray-200 text-gray-400' => !$isSelesai && !$isAktif,
                                    'cursor-pointer hover:scale-110' => $isClickable && !$isAktif,
                                    'cursor-not-allowed opacity-60' => !$isClickable && !$isAktif,
                                ]) tooltip-bottom="{{ $langkah->judul }}"
                                {{-- Tooltip --}}>
                                @if ($isSelesai)
                                    <x-icon name="o-check" class="w-6 h-6" />
                                @else
                                    {{-- Gunakan ikon yang sesuai dengan tipe langkah --}}
                                    @php
                                        $icons = [
                                            'video' => 'o-play-circle',
                                            'audio' => 'o-musical-note',
                                            'canva' => 'o-presentation-chart-line',
                                            'pdf' => 'o-book-open',
                                            'soal_esai' => 'o-pencil',
                                            'penilaian_akhir' => 'o-trophy',
                                        ];
                                    @endphp
                                    <x-icon :name="$icons[$langkah->tipe] ?? 'o-document-text'" class="w-6 h-6" />
                                @endif
                            </div>

                            {{-- Garis Penghubung (kecuali untuk yang terakhir) --}}
                            @if (!$loop->last)
                                <div
                                    class="flex-grow h-1 rounded-full {{ $isSelesai || $isAktif ? 'bg-primary' : 'bg-gray-200' }}">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        {{-- ======================================================= --}}
        {{--                 AREA KONTEN UTAMA (PLAYER)              --}}
        {{-- ======================================================= --}}
        <main class="flex-grow overflow-y-auto p-4">
            <div class="max-w-5xl mx-auto">
                @if (!$semuaSelesai && $langkahAktif)
                    <div wire:key="langkah-{{ $langkahAktif->id }}" class="mx-auto" x-data x-init="gsap.from($el, { opacity: 0, y: 20, duration: 0.5, ease: 'power2.out' })">
                        @php
                            $kondisi = $langkahAktif->kondisi_selesai ?? ['tipe' => 'manual'];
                            // Paksa kondisi selesai untuk Admin/Guru
                            if (in_array(auth()->user()->role, ['Admin', 'Guru'])) {
                                $kondisi['tipe'] = 'manual';
                            }
                        @endphp

                        {{-- VIEWER: VIDEO, AUDIO, CANVA, PDF (semua yang bisa pakai timer/otomatis) --}}
                        @if (in_array($langkahAktif->tipe, ['video', 'audio', 'canva', 'pdf']))
                            <div x-data="{
                                kondisi: {{ json_encode($kondisi) }},
                                init() {
                                    // Jika kondisi 'otomatis' (misal untuk video/audio)
                                    if (this.kondisi.tipe === 'otomatis') {
                                        const mediaElement = this.$el.querySelector('video, audio');
                                        if (mediaElement) {
                                            mediaElement.onended = () => {
                                                $wire.aktifkanTombolLanjut();
                                            };
                                        }
                                    }
                                    // Jika kondisi 'timer' (untuk materi baca)
                                    else if (this.kondisi.tipe === 'timer' && this.kondisi.detik > 0) {
                                        let sisaWaktu = this.kondisi.detik;
                                        const timerEl = this.$refs.timer;
                            
                                        const timer = setInterval(() => {
                                            sisaWaktu--;
                                            if (timerEl) {
                                                let minutes = Math.floor(sisaWaktu / 60);
                                                let seconds = sisaWaktu % 60;
                                                timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                                            }
                                            if (sisaWaktu <= 0) {
                                                $wire.aktifkanTombolLanjut();
                                                clearInterval(timer);
                                            }
                                        }, 1000);
                                    }
                                    // Jika tidak ada aturan spesifik ('manual')
                                    else {
                                        $wire.aktifkanTombolLanjut();
                                    }
                                }
                            }">
                                <h2 class="text-2xl font-bold mb-2 text-gray-800">{{ $langkahAktif->judul }}</h2>

                                {{-- Tampilkan timer jika ada --}}
                                @if (($kondisi['tipe'] ?? '') === 'timer')
                                    <div class="text-center p-2 bg-gray-200 rounded-full inline-block">
                                        Waktu minimum: <span x-ref="timer"
                                            class="font-bold font-mono">{{ gmdate('i:s', $kondisi['detik']) }}</span>
                                    </div>
                                @endif

                                {{-- Render konten --}}
                                @if ($langkahAktif->tipe === 'video')
                                    <div class="bg-black rounded-lg aspect-video shadow-lg">
                                        <video width="100%" height="100%" controls class="rounded-lg">
                                            <source src="{{ asset($langkahAktif->konten_path) }}" type="video/mp4">
                                        </video>
                                    </div>
                                @elseif ($langkahAktif->tipe === 'audio')
                                    <div class="flex flex-col items-center justify-center">

                                        {{-- KONTENER PEMUTAR AUDIO UTAMA --}}
                                        <div class="w-full rounded-2xl shadow-2xl overflow-hidden bg-gray-800"
                                            x-data="{
                                                isPlaying: false,
                                                progress: 0,
                                                currentTime: '00:00',
                                                totalTime: '00:00',
                                            
                                                initAudio() {
                                                    const audio = this.$refs.audioPlayer;
                                            
                                                    // Saat metadata audio dimuat, dapatkan durasi total
                                                    audio.addEventListener('loadedmetadata', () => {
                                                        this.totalTime = this.formatTime(audio.duration);
                                                    });
                                            
                                                    // Update progress bar dan waktu saat audio diputar
                                                    audio.addEventListener('timeupdate', () => {
                                                        this.progress = (audio.currentTime / audio.duration) * 100;
                                                        this.currentTime = this.formatTime(audio.currentTime);
                                                    });
                                            
                                                    // Saat audio selesai
                                                    audio.addEventListener('ended', () => {
                                                        this.isPlaying = false;
                                                        this.progress = 100;
                                                        $wire.aktifkanTombolLanjut(); // Aktifkan tombol lanjut di induk
                                                    });
                                                },
                                            
                                                togglePlay() {
                                                    const audio = this.$refs.audioPlayer;
                                                    if (audio.paused) {
                                                        audio.play();
                                                    } else {
                                                        audio.pause();
                                                    }
                                                },
                                            
                                                // Fungsi helper untuk format waktu
                                                formatTime(seconds) {
                                                    if (isNaN(seconds)) return '00:00';
                                                    const min = Math.floor(seconds / 60);
                                                    const sec = Math.floor(seconds % 60);
                                                    return `${String(min).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
                                                }
                                            }" x-init="initAudio()">
                                            {{-- Bagian Cover Art --}}
                                            {{-- Bagian Cover Art dengan Gambar Utuh --}}
                                            <div class="relative w-full bg-black">
                                                {{-- Gunakan rasio aspek 16:9 untuk tampilan sinematik --}}
                                                <div class="aspect-w-16 aspect-h-9">
                                                    <img src="{{ asset('assets/img/background-audio.jpg') }}"
                                                        alt="{{ $langkahAktif->judul }}"
                                                        class="w-full h-full object-contain">
                                                </div>
                                                {{-- Tombol Play/Pause di Tengah --}}
                                                <div class="absolute bottom-0 inset-x-0 w-full p-6 text-white">
                                                    {{-- Tombol Play/Pause --}}
                                                    <button @@click="togglePlay()"
                                                        class="w-16 h-16 rounded-full bg-primary text-white flex-shrink-0 flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform">
                                                        <x-icon x-show="!isPlaying" name="s-play" class="w-10 h-10" />
                                                        <x-icon x-show="isPlaying" name="s-pause" class="w-10 h-10" />
                                                    </button>
                                                </div>
                                            </div>
                                            {{-- Bagian Bawah: Info & Kontrol --}}
                                            <div class="p-6">
                                                {{-- Info Trek --}}
                                                <div class="text-center mb-4">
                                                    <h3 class="font-bold text-2xl text-white truncate">
                                                        {{ $langkahAktif->judul }}</h3>
                                                    <p class="text-sm text-gray-400">SIJAKA Audio</p>
                                                </div>

                                                {{-- Progress Bar & Waktu --}}
                                                <div class="w-full">
                                                    <div class="w-full bg-gray-600 rounded-full h-2">
                                                        <div class="bg-primary h-2 rounded-full"
                                                            :style="`width: ${progress}%`">
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="flex justify-between text-xs text-gray-400 font-mono mt-1">
                                                        <span x-text="currentTime"></span>
                                                        <span x-text="totalTime"></span>
                                                    </div>
                                                </div>

                                                {{-- Visualizer (opsional, bisa dihapus jika ingin lebih bersih) --}}
                                                <div class="flex justify-center items-end gap-1 h-16 mt-4">
                                                    <template x-for="i in 25" :key="i">
                                                        <div class="w-1.5 bg-gray-500 rounded-full"
                                                            :class="{ 'animate-pulse': isPlaying }"
                                                            :style="`animation-duration: ${Math.random() * 0.5 + 0.3}s; height: ${Math.random() * 100}%`">
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {{-- Elemen Audio yang Sebenarnya (Disembunyikan) --}}
                                            <audio x-ref="audioPlayer" preload="metadata"
                                                @@play="isPlaying = true"
                                                @@pause="isPlaying = false" class="hidden">
                                                <source src="{{ asset($langkahAktif->konten_path) }}"
                                                    type="audio/mpeg">
                                            </audio>
                                        </div>
                                    </div>
                                    {{-- VIEWER: EMBED CANVA --}}
                                @elseif($langkahAktif->tipe === 'canva')
                                    <div class="relative w-full h-0 pb-[56.25%] rounded-lg overflow-hidden shadow-lg"
                                        style="position: relative; width: 100%; height: 0; padding-top: 56.2500%; padding-bottom: 0; box-shadow: 0 2px 8px 0 rgba(63,69,81,0.16); margin-top: 1.6em; margin-bottom: 0.9em; overflow: hidden; border-radius: 8px; will-change: transform;">
                                        <iframe loading="lazy"
                                            style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0;margin: 0;"
                                            src="{{ $langkahAktif->konten_path }}?embed"
                                            allowfullscreen="allowfullscreen" allow="fullscreen">
                                        </iframe>
                                    </div>
                                    {{-- VIEWER: PDF FLIP BOOK --}}
                                @elseif($langkahAktif->tipe === 'pdf')
                                    <div class="relative w-full h-0 pb-[56.25%] rounded-lg overflow-hidden shadow-lg"
                                        style="position: relative; width: 100%; height: 0; padding-top: 56.2500%; padding-bottom: 0; box-shadow: 0 2px 8px 0 rgba(63,69,81,0.16); margin-top: 1.6em; margin-bottom: 0.9em; overflow: hidden; border-radius: 8px; will-change: transform;">
                                        <iframe loading="lazy"
                                            style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; border: none; padding: 0;margin: 0;"
                                            src="{{ $langkahAktif->konten_path }}?embed"
                                            allowfullscreen="allowfullscreen" allow="fullscreen">
                                        </iframe>
                                    </div>
                                @endif
                            </div>

                            {{-- VIEWER: SOAL ESAI --}}
                        @elseif($langkahAktif->tipe === 'soal_esai')
                            <x-card :title="$langkahAktif->judul" icon="o-pencil-square" shadow>
                                <div
                                    class="prose max-w-none mb-6 prose-ul:list-disc prose-ol:list-decimal prose-li:pl-6">
                                    {!! $langkahAktif->konten_teks !!}
                                </div>
                                <x-form wire:submit="tandaiLangkahSelesai">
                                    <x-textarea label="Jawaban Anda" wire:model="jawabanEsai" rows="6" />
                                    <x-slot:actions>
                                        <x-button label="Kirim Jawaban & Lanjut" type="submit" class="btn-primary"
                                            spinner="tandaiLangkahSelesai" />
                                    </x-slot:actions>
                                </x-form>
                            </x-card>

                            {{-- VIEWER: PENILAIAN AKHIR --}}
                        @elseif($langkahAktif->tipe === 'penilaian_akhir')
                            {{-- Komponen runner tidak memerlukan tombol "Lanjut" eksternal --}}
                            <livewire:pembelajaran.penilaian-runner />
                        @endif

                        {{-- ======================================================= --}}
                        {{--          TOMBOL LANJUT YANG DIKONTROL INDUK           --}}
                        {{-- ======================================================= --}}
                        @if (
                            !in_array($langkahAktif->tipe, ['soal_esai', 'penilaian_akhir']) &&
                                !$langkahSelesaiIds->contains($langkahAktif->id))
                            <div class="mt-4 text-center h-16">
                                {{-- Untuk Admin/Guru, tombol ini akan selalu aktif --}}
                                @if ($bisaLanjut || in_array(auth()->user()->role, ['Admin', 'Guru']))
                                    <x-button label="Langkah Berikutnya" wire:click="tandaiLangkahSelesai"
                                        class="btn-primary btn-lg" spinner="tandaiLangkahSelesai" />
                                @else
                                    <x-button label="Selesaikan aktivitas untuk melanjutkan..."
                                        class="btn-disabled btn-lg" />
                                @endif
                            </div>
                        @endif
                    </div>
                @else
                    {{-- Tampilan saat semua langkah selesai --}}
                    <div class="text-center h-full flex flex-col justify-center items-center mt-10" x-data
                        x-init="gsap.from($el.children, { scale: 0.8, opacity: 0, stagger: 0.1, duration: 0.5, ease: 'back.out(1.7)' })">
                        {{-- Ganti <x-icon> dengan komponen kustom kita --}}
                        <x-icons.party-popper class="w-24 h-24 animate-bounce" />

                        <h2 class="text-3xl font-bold mt-4 text-gray-800">Luar Biasa!</h2>
                        <p class="text-gray-600 mt-2 text-lg">Kamu telah menyelesaikan semua tantangan di Pulau
                            {{ Str::ucfirst($modul->nama_pulau) }}.</p>
                        <a href="{{ route('peta-petualangan') }}" wire:navigate
                            class="btn btn-primary mt-8 btn-lg rounded-full px-8">
                            Lanjutkan Petualangan Berikutnya!
                        </a>
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>
