<div class="w-full max-w-2xl mx-auto my-4 md:my-6">
    <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 relative">
        <a href="{{ route('welcome') }}" wire:navigate class="absolute top-4 left-4">
            <x-button label="Home" icon="o-arrow-left" class="btn-ghost btn-sm" tooltip="Kembali ke Halaman Utama" />
        </a>
        <div class="text-center mb-8">
            <img src="{{ asset('assets/img/logo/logo-sijaka.png') }}" alt="Logo SIJAKA" class="w-20 h-20 mx-auto mb-4">
            <div class="flex items-center justify-center gap-2">
                <h1 class="text-3xl font-bold text-gray-800">Buat Akun Baru</h1>
                <x-button icon="o-question-mark-circle" wire:click="$toggle('bantuanModal')"
                    class="btn-sm btn-circle btn-ghost" tooltip="Butuh Bantuan?" />
            </div>
            <p class="text-gray-500 mt-2">Pilih peranmu untuk memulai petualangan di SIJAKA!</p>
        </div>

        {{-- KONTENER TABS --}}
        <x-tabs wire:model.live="tabAktif">
            {{-- TAB GURU --}}
            <x-tab name="guru" label="Saya Guru" icon="o-academic-cap">
                <x-form wire:submit="registerGuru" class="mt-6 space-y-4">
                    <h3 class="font-bold text-lg border-b pb-2 mb-4">Informasi Akun Guru</h3>
                    <x-input label="Nama Lengkap" wire:model="nama_guru" />
                    <x-input label="Email" wire:model="email_guru" type="email" />
                    <x-input label="Password" wire:model="password_guru" type="password" />
                    <x-input label="Ulangi Password" wire:model="password_guru_confirmation" type="password" />

                    <h3 class="font-bold text-lg border-b pb-2 my-4 pt-4">Informasi Institusi</h3>
                    <x-input label="NPSN Sekolah" wire:model.live.debounce.500ms="npsn">
                        <x-slot:append>
                            <span wire:loading wire:target="npsn" class="loading loading-spinner loading-xs"></span>
                        </x-slot:append>
                    </x-input>

                    <x-input label="Nama Sekolah" wire:model="nama_sekolah" :disabled="$sekolahDitemukan" />

                    @if ($logoSekolahPreview)
                        <div class="p-2 border rounded-lg text-center">
                            <p class="text-sm text-gray-500 mb-2">Logo Sekolah yang sudah terdaftar:</p>
                            <img src="{{ $logoSekolahPreview }}" class="h-24 mx-auto object-contain">
                        </div>
                    @else
                        <x-file label="Logo Sekolah" wire:model="logo_sekolah" :disabled="$sekolahDitemukan" />
                    @endif

                    <x-input label="Nama Kelas Baru yang Diampu" wire:model="nama_kelas"
                        placeholder="Contoh: Kelas 4A - Pagi" />

                    <x-slot:actions>
                        <x-button label="Daftar sebagai Guru" type="submit" class="btn-primary w-full"
                            spinner="registerGuru" />
                    </x-slot:actions>
                </x-form>
            </x-tab>

            {{-- TAB SISWA --}}
            <x-tab name="siswa" label="Saya Siswa" icon="o-user">
                <x-form wire:submit="registerSiswa" class="mt-6 space-y-4">
                    <h3 class="font-bold text-lg border-b pb-2 mb-4">Informasi Akun Siswa</h3>
                    <x-input label="Nama Lengkap" wire:model="nama_siswa" />
                    <x-input label="Email" wire:model="email_siswa" type="email" />
                    <x-input label="Password" wire:model="password_siswa" type="password" />
                    <x-input label="Ulangi Password" wire:model="password_siswa_confirmation" type="password" />
                    <x-radio label="Jenis Kelamin" :options="[['id' => 'L', 'name' => 'Laki-laki'], ['id' => 'P', 'name' => 'Perempuan']]" wire:model="jk_siswa" />

                    <h3 class="font-bold text-lg border-b pb-2 my-4 pt-4">Informasi Kelas (Opsional)</h3>
                    <x-select label="Pilih Sekolah" :options="$this->sekolahOptions" wire:model.live="sekolah_id_siswa"
                        placeholder="Pilih jika sudah ada" option-value="id" option-label="nama">
                        {{-- Tambahkan slot 'append' jika sekolah sudah dipilih --}}
                        @if ($sekolah_id_siswa)
                            <x-slot:append>
                                <div class="flex flex-col justify-center">
                                    <x-button icon="o-x-mark" class="btn-sm btn-ghost text-red-500 h-full"
                                        wire:click="resetPilihanKelasSiswa" spinner="resetPilihanKelasSiswa"
                                        tooltip="Hapus Pilihan" />
                                </div>
                            </x-slot:append>
                        @endif
                    </x-select>

                    {{-- SELECT KELAS --}}
                    <x-select label="Pilih Kelas" :options="$this->kelasOptions" wire:model="kelas_id_siswa"
                        placeholder="Pilih jika sudah ada" option-value="id" option-label="nama" :disabled="!$sekolah_id_siswa" />

                    <x-slot:actions>
                        <x-button label="Daftar sebagai Siswa" type="submit" class="btn-primary w-full"
                            spinner="registerSiswa" />
                    </x-slot:actions>
                </x-form>
            </x-tab>
        </x-tabs>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-500">
                Sudah punya akun?
                <a href="{{ route('login') }}" wire:navigate class="font-semibold text-primary hover:underline">
                    Masuk di sini!
                </a>
            </p>
        </div>
    </div>

    <x-modal wire:model="bantuanModal" title="Petunjuk Pendaftaran">

        {{-- ============================================= --}}
        {{--         KONTEN MODAL YANG DINAMIS             --}}
        {{-- ============================================= --}}

        @if ($tabAktif === 'guru')
            <div class="space-y-6">
                <div class="flex items-center gap-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg">
                    <x-icon name="o-academic-cap" class="w-12 h-12 text-blue-600 flex-shrink-0" />
                    <div>
                        <h3 class="font-bold text-lg text-blue-800">Selamat Datang, Bapak/Ibu Guru!</h3>
                        <p class="text-gray-600">Ikuti panduan ini untuk mendaftarkan akun dan kelas pertama Anda.</p>
                    </div>
                </div>

                <div class="space-y-4 text-gray-700">
                    <p><strong>1. Informasi Akun:</strong><br>Isi nama lengkap, email, dan password Anda untuk keamanan.
                    </p>
                    <p><strong>2. Informasi Institusi (Pengecekan Otomatis):</strong><br>Cukup ketik
                        <strong>NPSN</strong> sekolah Anda. Jika sekolah sudah ada di sistem, nama dan logo akan terisi
                        otomatis. Jika belum, Anda bisa mendaftarkannya.
                    </p>
                    <p><strong>3. Kelas Pertama Anda:</strong><br>Masukkan nama untuk kelas baru yang akan langsung Anda
                        ampu setelah mendaftar.</p>
                </div>
            </div>
        @endif

        {{-- KONTEN UNTUK SISWA --}}
        @if ($tabAktif === 'siswa')
            <div class="space-y-6">
                <div class="flex items-center gap-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg">
                    <x-icon name="o-user" class="w-12 h-12 text-green-600 flex-shrink-0" />
                    <div>
                        <h3 class="font-bold text-lg text-green-800">Hai, Calon Petualang SIJAKA!</h3>
                        <p class="text-gray-600">Membuat akun itu mudah, ikuti langkah-langkah ini ya!</p>
                    </div>
                </div>

                <div class="space-y-4 text-gray-700">
                    <p><strong>1. Data Dirimu:</strong><br>Isi nama lengkap, email, dan password rahasiamu. Pilih juga
                        jenis kelaminmu.</p>
                    <p><strong>2. Pilih Kelasmu (Boleh Dikosongkan):</strong><br>Jika gurumu sudah memberitahu, kamu
                        bisa langsung memilih <strong>Sekolah</strong> dan <strong>Kelas</strong>. Jika belum, tidak
                        apa-apa, kamu tetap bisa mendaftar dan belajar mandiri!</p>
                    <p><strong>3. Siap Berpetualang!</strong><br>Setelah semua terisi, klik tombol "Daftar" dan
                        petualangan belajarmu di SIJAKA akan dimulai!</p>
                </div>
            </div>
        @endif
        {{-- ============================================= --}}


        <x-slot:actions>
            <x-button label="Saya Mengerti" @click="$wire.bantuanModal = false" class="btn-primary" />
        </x-slot:actions>
    </x-modal>
</div>
