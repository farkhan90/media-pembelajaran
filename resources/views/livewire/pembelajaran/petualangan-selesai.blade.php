<div class="w-full h-screen flex flex-col items-center justify-center p-8 bg-gradient-to-br from-yellow-200 via-amber-200 to-orange-200 text-center"
    x-data x-init="// Inisialisasi animasi confetti
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };
    const randomInRange = (min, max) => Math.random() * (max - min) + min;
    
    const interval = setInterval(() => {
        confetti({
            ...defaults,
            particleCount: 100,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        });
        confetti({
            ...defaults,
            particleCount: 100,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        });
    }, 500);
    
    // Hentikan confetti setelah beberapa saat
    setTimeout(() => clearInterval(interval), 5000);
    
    // Animasi untuk konten
    gsap.from($refs.content.children, {
        scale: 0.8,
        opacity: 0,
        y: 50,
        stagger: 0.2,
        duration: 1,
        ease: 'back.out(1.7)'
    });">
    {{-- Kontainer untuk animasi --}}
    <div x-ref="content">
        <x-icon name="s-trophy" class="w-32 h-32 mx-auto text-yellow-500 drop-shadow-lg" />

        <h1 class="text-4xl md:text-6xl font-lilita text-gray-800 mt-6"
            style="-webkit-text-stroke: 1px #FBBF24; text-stroke: 1px #FBBF24;">
            SELAMAT!
        </h1>

        <h2 class="text-2xl md:text-3xl font-bold text-gray-700 mt-2">
            Kamu Telah Menyelesaikan Semua Petualangan!
        </h2>

        <p class="text-gray-600 mt-4 max-w-xl mx-auto text-lg">
            Semua pengetahuan dari setiap pulau telah kamu kuasai. Sekarang, semua pulau telah terbuka dan kamu bisa
            mengunjunginya kembali kapan saja untuk belajar lagi.
        </p>

        <div class="mt-10">
            <a href="{{ route('peta-petualangan') }}" wire:navigate
                class="btn btn-primary btn-lg rounded-full px-10 transform hover:scale-105 transition-transform shadow-lg">
                <x-icon name="o-map" class="w-6 h-6 mr-2" />
                Kembali ke Peta (Mode Jelajah Bebas)
            </a>
        </div>
    </div>
</div>
