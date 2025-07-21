<div x-data="{}" x-init="gsap.from($el, { scale: 0.9, opacity: 0, duration: 0.5, ease: 'back.out(1.7)' })">
    {{-- KARTU UTAMA DENGAN DUA KOLOM --}}
    <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl flex flex-col lg:flex-row overflow-hidden">

        {{-- KOLOM KIRI: ILUSTRASI --}}
        <div class="w-full lg:w-1/2 p-8 bg-blue-50 flex-col justify-center items-center hidden lg:flex">
            {{-- GANTI DENGAN KODE SVG DARI UNDRAW ATAU STORYSET --}}
            <img src="{{ asset('assets/img/login-illustration.svg') }}" alt="Ilustrasi Belajar" class="w-full max-w-sm">
            {{-- Pastikan Anda menaruh file ilustrasi di public/assets/img/ --}}

            <h2 class="text-2xl font-bold text-blue-800 mt-8">Mulai Petualangan Belajarmu!</h2>
            <p class="text-blue-600 mt-2 text-center">Masukkan nama pengguna dan kata sandi untuk masuk ke dunia
                pengetahuan.</p>
        </div>

        {{-- KOLOM KANAN: FORM LOGIN --}}
        <div class="w-full lg:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <h2 class="text-3xl font-bold text-gray-800 text-center lg:text-left">Selamat Datang Kembali!</h2>
            <p class="text-gray-500 text-center lg:text-left mt-2 mb-8">Siap untuk belajar hal baru hari ini?</p>

            <x-form wire:submit="login" class="space-y-6">
                {{-- Input Nama Pengguna / Email --}}
                <x-input label="Nama Pengguna atau Email" wire:model="email" icon="o-user"
                    placeholder="Ketik di sini..." class="input-lg" {{-- Ukuran input lebih besar --}} inline />

                {{-- Input Password dengan Tombol Lihat --}}
                <div x-data="{ showPassword: false }">
                    <x-input label="Kata Sandi" wire:model="password" icon="o-key" placeholder="Ketik kata sandimu..."
                        class="input-lg" x-bind:type="showPassword ? 'text' : 'password'" inline>
                        <x-slot:append>
                            <div class="flex">
                                <x-button :class="showPassword ? 'btn-primary' : 'btn-ghost'" x-on:click.prevent="showPassword = !showPassword"
                                    icon="o-eye" class="btn-ghost h-full" />
                            </div>
                        </x-slot:append>
                    </x-input>
                </div>

                {{-- Tombol Login --}}
                <x-slot:actions>
                    <x-button label="Masuk Sekarang!" type="submit" icon-right="o-arrow-right-on-rectangle"
                        class="btn-primary btn-lg w-full" {{-- Tombol besar dan lebar penuh --}} spinner="login" />
                </x-slot:actions>
            </x-form>
        </div>
    </div>
</div>
