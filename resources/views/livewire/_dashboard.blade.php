<div x-data>
    {{-- ======================================================= --}}
    {{--                     TAMPILAN ADMIN                      --}}
    {{-- ======================================================= --}}
    @if (auth()->user()->role === 'Admin')
        <div x-init="gsap.from($el.children, { y: 20, opacity: 0, stagger: 0.1, duration: 0.5 })">
            <x-header title="Selamat Datang, Admin!" separator progress-indicator />
            {{-- <x-header title="Selamat Datang, Admin!" subtitle="Ringkasan Sistem Media Pembelajaran" separator /> --}}

            {{-- STATISTIK UTAMA --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-4">
                <x-stat title="Total Sekolah" :value="$this->totalSekolah" icon="o-building-office-2"
                    class="bg-blue-100 text-blue-800" />
                <x-stat title="Total Guru" :value="$this->totalGuru" icon="o-user-group" class="bg-green-100 text-green-800" />
                <x-stat title="Total Siswa" :value="$this->totalSiswa" icon="o-identification"
                    class="bg-yellow-100 text-yellow-800" />
                <x-stat title="Administrator" :value="$this->totalAdmin" icon="o-key" class="bg-red-100 text-red-800" />
            </div>

            @if ($this->chartSkorAntarSekolah())
                <div class="mt-8 p-6 bg-base-100 rounded-lg shadow">
                    <h2 class="text-xl font-bold mb-4">Performa Rata-rata per Sekolah</h2>
                    {{-- GANTI <x-chart> DENGAN <canvas> dan x-data --}}
                    <div class="h-96" x-data="{
                        {{-- Gunakan @js untuk mengubah data PHP/Livewire menjadi objek JavaScript yang aman --}}
                        chartData: @js($this->chartSkorAntarSekolah()),
                            init() {
                                // Hentikan inisialisasi jika tidak ada data
                                if (!this.chartData) {
                                    return;
                                }
                                const ctx = this.$refs.chartCanvas.getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: this.chartData,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 100
                                            }
                                        }
                                    }
                                });
                            }
                    }" wire:ignore>
                        <canvas x-ref="chartCanvas"></canvas>
                    </div>
                </div>
            @endif

            {{-- QUICK ACTIONS --}}
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4">Akses Cepat</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('users.index') }}" wire:navigate
                        class="p-6 bg-base-100 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                        <x-icon name="o-users" class="w-10 h-10 mx-auto text-primary" />
                        <p class="mt-2 font-semibold">Manajemen User</p>
                    </a>
                    <a href="{{ route('sekolah.index') }}" wire:navigate
                        class="p-6 bg-base-100 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                        <x-icon name="o-building-office-2" class="w-10 h-10 mx-auto text-success" />
                        <p class="mt-2 font-semibold">Manajemen Sekolah</p>
                    </a>
                    <a href="{{ route('kelas.index') }}" wire:navigate
                        class="p-6 bg-base-100 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                        <x-icon name="o-table-cells" class="w-10 h-10 mx-auto text-warning" />
                        <p class="mt-2 font-semibold">Manajemen Kelas</p>
                    </a>
                    <a href="{{ route('siswa.manage') }}" wire:navigate
                        class="p-6 bg-base-100 rounded-lg shadow text-center hover:shadow-lg transition-shadow">
                        <x-icon name="o-identification" class="w-10 h-10 mx-auto text-error" />
                        <p class="mt-2 font-semibold">Siswa per Kelas</p>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{--                      TAMPILAN GURU                      --}}
    {{-- ======================================================= --}}
    @if (auth()->user()->role === 'Guru')
        <div x-init="gsap.from($el.children, { y: 20, opacity: 0, stagger: 0.1, duration: 0.5 })">
            <x-header title="Selamat Datang, {{ auth()->user()->nama }}!" subtitle="Ringkasan aktivitas mengajar Anda."
                separator />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <x-stat title="Kelas Diampu" :value="$this->kelasDiampu->count()" icon="o-table-cells" class="bg-sky-100 text-sky-800" />
                <x-stat title="Total Siswa Anda" :value="$this->totalSiswaDiampu" icon="o-identification"
                    class="bg-purple-100 text-purple-800" />
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <x-card title="Kelas yang Anda Ampu" icon="o-list-bullet">
                    @forelse($this->kelasDiampu as $kelas)
                        <x-list-item :item="$kelas" no-hover>
                            <x-slot:value>
                                {{ $kelas->nama }}
                            </x-slot:value>
                            <x-slot:sub-value>
                                {{ $kelas->sekolah->nama }}
                            </x-slot:sub-value>
                        </x-list-item>
                    @empty
                        <p>Anda belum dihubungkan ke kelas manapun.</p>
                    @endforelse
                </x-card>

                @if ($this->chartSkorAntarKelas())
                    <div class="mt-8 p-6 bg-base-100 rounded-lg shadow">
                        <h2 class="text-xl font-bold mb-4">Performa Rata-rata per Kelas Anda</h2>
                        <div class="h-96" x-data="{
                            chartData: @js($this->chartSkorAntarKelas()),
                            init() {
                                const ctx = this.$refs.chartCanvas.getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: this.chartData,
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                max: 100
                                            }
                                        }
                                    }
                                });
                            }
                        }" wire:ignore>
                            <canvas x-ref="chartCanvas"></canvas>
                        </div>
                    </div>
                @endif

                <x-card title="Akses Cepat" icon="o-bolt">
                    <div class="flex flex-col gap-4">
                        <a href="{{ route('siswa.manage') }}" wire:navigate
                            class="p-4 bg-base-200 rounded-lg flex items-center gap-4 hover:bg-base-300">
                            <x-icon name="o-identification" class="w-8 h-8 text-primary" />
                            <span>Kelola Siswa di Kelas Anda</span>
                        </a>
                    </div>
                </x-card>
            </div>
        </div>
    @endif

    {{-- ======================================================= --}}
    {{--                      TAMPILAN SISWA                     --}}
    {{-- ======================================================= --}}
    @if (auth()->user()->role === 'Siswa')
        <div x-init="gsap.from($el.children, { y: 20, opacity: 0, stagger: 0.1, duration: 0.5 })">
            <x-header title="Hai, {{ auth()->user()->nama }}!" subtitle="Selamat belajar dan tetap semangat!"
                separator />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <x-stat title="Rata-rata Skor" value="{{ round($this->rataRataSkor, 2) }}" icon="o-star"
                    class="bg-yellow-100 text-yellow-800" />
                <x-stat title="Tugas Baru" :value="$this->tugasBelumDikerjakan->count()" icon="o-bell-alert" class="bg-red-100 text-red-800" />
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Tugas yang Belum Dikerjakan --}}
                <x-card title="Tugas Baru Untukmu!" icon="o-sparkles" class="bg-primary text-primary-content">
                    @forelse($this->tugasBelumDikerjakan as $tugas)
                        @php
                            $isUjian = $tugas instanceof \App\Models\Ujian;
                            $route = $isUjian
                                ? route('ujian.kerjakan', $tugas->slug)
                                : route('kuis.kerjakan', $tugas->id);
                        @endphp
                        <x-list-item :item="$tugas" no-hover>
                            <x-slot:avatar>
                                <x-icon :name="$isUjian ? 'o-academic-cap' : 'o-puzzle-piece'" class="w-8 h-8" />
                            </x-slot:avatar>
                            <x-slot:value>
                                {{ $tugas->judul }}
                            </x-slot:value>
                            <x-slot:sub-value>
                                {{ $isUjian ? 'Kuis Pilihan Ganda' : 'Kuis Menjodohkan' }}
                            </x-slot:sub-value>
                            <x-slot:actions>
                                <x-button label="Kerjakan" :link="$route" wire:navigate
                                    class="btn-sm btn-outline btn-ghost" />
                            </x-slot:actions>
                        </x-list-item>
                    @empty
                        <div class="text-center p-4">
                            <x-icon name="o-check-badge" class="w-12 h-12 mx-auto" />
                            <p class="mt-2 font-semibold">Hebat! Semua tugas sudah selesai dikerjakan.</p>
                        </div>
                    @endforelse
                </x-card>

                {{-- Riwayat Terbaru --}}
                <x-card title="Riwayat Pengerjaan Terakhir" icon="o-clock">
                    @forelse($this->riwayatTerbaru as $histori)
                        @php
                            $isUjian = $histori instanceof \App\Models\HistoriUjian;
                            $route = $isUjian ? route('ujian.hasil') : route('kuis.hasil');
                            $judul = $isUjian ? $histori->ujian->judul : $histori->kuis->judul;
                        @endphp
                        <x-list-item :item="$histori" no-hover>
                            <x-slot:value>
                                {{ Str::limit($judul, 30) }}
                            </x-slot:value>
                            <x-slot:sub-value>
                                {{ \Carbon\Carbon::parse($histori->waktu_selesai)->diffForHumans() }}
                            </x-slot:sub-value>
                            <x-slot:actions>
                                <x-badge :value="'Skor: ' . round($histori->skor_akhir, 2)" class="badge-ghost" />
                            </x-slot:actions>
                        </x-list-item>
                    @empty
                        <p>Kamu belum pernah mengerjakan Kuis.</p>
                    @endforelse
                    <div class="mt-4">
                        <x-button label="Lihat Semua Hasil" link="{{ route('ujian.hasil') }}" wire:navigate
                            class="btn-sm btn-ghost" />
                    </div>
                </x-card>
            </div>
        </div>
    @endif
</div>
