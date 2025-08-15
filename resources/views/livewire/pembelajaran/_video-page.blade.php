<div class="w-full h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-4 md:p-8 relative"
    x-data="{
        timerBerjalan: false,
        timerSelesai: false,
        sisaWaktu: {{ $durasiDetik }},
        player: null,
    
        initPlayer() {
            // Definisikan fungsi onYouTubeIframeAPIReady secara global
            // Pastikan ia hanya didefinisikan sekali
            if (!window.onYouTubeIframeAPIReady) {
                window.onYouTubeIframeAPIReady = () => {
                    // Saat API siap, dispatch event kustom
                    window.dispatchEvent(new Event('youtube-api-ready'));
                };
    
                // Muat skrip API
                const tag = document.createElement('script');
                tag.src = 'https://www.youtube.com/iframe_api';
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }
    
            // Fungsi untuk membuat player
            const createPlayer = () => {
                console.log('Mencoba membuat player untuk video ID:', '{{ $videoId }}');
                this.player = new YT.Player('youtube-player-placeholder', {
                    height: '100%',
                    width: '100%',
                    videoId: '{{ $videoId }}',
                    playerVars: {
                        'autoplay': 0, // Jangan autoplay, biarkan siswa mengklik
                        'controls': 1,
                        'rel': 0 // Sembunyikan video terkait
                    },
                    events: {
                        'onReady': (event) => console.log('Player is ready.'),
                        'onStateChange': this.onPlayerStateChange.bind(this)
                    }
                });
            };
    
            // Cek apakah API sudah siap
            if (window.YT && window.YT.Player) {
                createPlayer();
            } else {
                // Jika belum, tunggu event kustom kita
                window.addEventListener('youtube-api-ready', createPlayer, { once: true });
            }
        },
    
        onPlayerStateChange(event) {
            console.log('Player state changed:', event.data);
    
            if (event.data === YT.PlayerState.PLAYING && !this.timerBerjalan) {
                console.log('Video PLAYING, memulai timer...');
                this.startTimer();
                this.timerBerjalan = true;
            }
        },
    
        // 4. Fungsi untuk memulai timer
        startTimer() {
            const timer = setInterval(() => {
                this.sisaWaktu--;
                if (this.sisaWaktu <= 0) {
                    this.timerSelesai = true;
                    clearInterval(timer);
                }
            }, 1000);
        },
    
        // 5. Fungsi format waktu
        formatWaktu() {
            if (this.sisaWaktu <= 0) return '00:00';
            let minutes = Math.floor(this.sisaWaktu / 60);
            let seconds = this.sisaWaktu % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }
    }"x-init="initPlayer()">

    {{-- Tombol Navigasi di Pojok Atas --}}
    <div class="absolute top-6 left-6 md:top-8 md:left-8 z-10">
        {{-- Tombol Kembali hanya untuk Admin/Guru --}}
        @if (in_array(auth()->user()->role, ['Admin', 'Guru']))
            <a href="{{ route('peta-petualangan') }}" wire:navigate>
                <x-button label="Kembali ke Peta" icon="o-arrow-left" class="btn-ghost text-white hover:bg-white/20" />
            </a>
        @endif
    </div>

    {{-- Konten Utama dengan Animasi Masuk --}}
    <div class="text-center w-full" x-data x-init="gsap.from($el, { y: 30, opacity: 0, duration: 0.8, ease: 'power2.out' })">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">{{ $judul }}</h1>
        <p class="text-gray-400 mb-8">Tonton video ini sampai selesai untuk membuka pulau berikutnya!</p>

        {{-- Kontainer Video --}}
        <div class="w-full max-w-4xl mx-auto aspect-video bg-black rounded-xl shadow-2xl shadow-primary/20">
            <div id="youtube-player-placeholder" class="w-full h-full"></div>
        </div>

        {{-- Tombol Lanjut (Hanya untuk Siswa) --}}
        <div class="mt-8 h-16">
            @if (auth()->user()->role === 'Siswa')
                <div x-show="timerSelesai" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-end="opacity-100 scale-100">
                    <x-button label="Lanjut ke Pulau {{ Str::ucfirst($pulauBerikutnya) }}" wire:click="tandaiSelesai"
                        class="btn-primary btn-lg animate-pulse rounded-full px-8" icon-right="o-arrow-right"
                        spinner="tandaiSelesai" />
                </div>

                {{-- Tampilkan hitungan mundur HANYA JIKA timer berjalan --}}
                <div x-show="timerBerjalan && !timerSelesai" x-transition>
                    <x-button class="btn-disabled btn-lg rounded-full px-8" icon-right="o-clock">
                        <span>Waktu Tersisa</span>
                        <span x-text="formatWaktu()" class="font-mono ml-2"></span>
                    </x-button>
                </div>

                {{-- Tampilkan pesan 'belum mulai' JIKA timer belum berjalan --}}
                <div x-show="!timerBerjalan" x-transition>
                    <x-button label="Silakan Putar Videonya" class="btn-disabled btn-lg rounded-full px-8"
                        icon-right="o-video-camera" />
                </div>
            @endif
        </div>
    </div>
</div>
