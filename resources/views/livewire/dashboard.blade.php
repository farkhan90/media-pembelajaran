<div x-data>
    {{-- TAMPILAN ADMIN --}}
    @if (auth()->user()->role === 'Admin')
        <div x-init="gsap.from($el.children, { y: 20, opacity: 0, stagger: 0.1, duration: 0.5 })">
            <x-header title="Dashboard Administrator" subtitle="Ringkasan dan kontrol penuh atas Sistem SIJAKA."
                separator />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                {{-- Kolom Utama (Peringkat Sekolah) --}}
                <div class="lg:col-span-2">
                    @if ($this->chartSkorAntarSekolah())
                        <x-card title="Performa Rata-rata per Sekolah" icon="o-chart-bar" shadow>
                            {{-- Kontainer untuk Grafik dengan Alpine.js --}}
                            <div class="h-96" x-data="{
                                chartData: @js($this->chartSkorAntarSekolah()),
                                init() {
                                    if (!this.chartData) return;
                            
                                    const ctx = this.$refs.chartCanvasAdmin.getContext('2d');
                                    new Chart(ctx, {
                                        type: 'bar',
                                        data: this.chartData,
                                        options: {{ json_encode($chartOptions) }}
                                    });
                                }
                            }" x-init="init()" wire:ignore
                                {{-- SANGAT PENTING --}}>
                                <canvas x-ref="chartCanvasAdmin"></canvas>
                            </div>
                        </x-card>
                    @else
                        <x-alert title="Data Grafik Belum Tersedia"
                            description="Grafik akan muncul setelah ada siswa yang menyelesaikan ujian atau kuis."
                            icon="o-information-circle" />
                    @endif
                </div>

                {{-- Kolom Samping (Statistik & Akses Cepat) --}}
                <div class="lg:col-span-1 space-y-8">
                    <x-card title="Statistik Pengguna" icon="o-users" shadow>
                        <div class="space-y-4">
                            <x-stat title="Total Guru" :value="$this->totalGuru()" icon="o-user-group"
                                class="bg-green-50 text-green-800" />
                            <x-stat title="Total Siswa" :value="$this->totalSiswa()" icon="o-identification"
                                class="bg-yellow-50 text-yellow-800" />
                            <x-stat title="Administrator" :value="$this->totalAdmin()" icon="o-key"
                                class="bg-red-50 text-red-800" />
                        </div>
                    </x-card>
                    <x-card title="Akses Cepat Manajemen" icon="o-bolt" shadow>
                        <div class="space-y-2">
                            <a href="{{ route('sekolah.index') }}" wire:navigate
                                class="block p-3 rounded-lg hover:bg-base-200 transition-colors">Kelola Sekolah</a>
                            <a href="{{ route('kelas.index') }}" wire:navigate
                                class="block p-3 rounded-lg hover:bg-base-200 transition-colors">Kelola Kelas</a>
                            <a href="{{ route('users.index') }}" wire:navigate
                                class="block p-3 rounded-lg hover:bg-base-200 transition-colors">Kelola User</a>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    @endif

    {{-- TAMPILAN GURU --}}
    @if (auth()->user()->role === 'Guru')
        <div x-init="gsap.from($el.children, { y: 20, opacity: 0, stagger: 0.1, duration: 0.5 })">
            <x-header title="Beranda Guru" subtitle="Selamat datang kembali, {{ auth()->user()->nama }}!" separator />
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">

                <div class="lg:col-span-2">
                    @if ($this->chartSkorAntarKelas())
                        <x-card title="Performa Rata-rata per Kelas Anda" icon="o-chart-pie" shadow>
                            <div class="h-96" x-data="{
                                chartData: @js($this->chartSkorAntarKelas()),
                                init() {
                                    if (!this.chartData) return;
                            
                                    const ctx = this.$refs.chartCanvasGuru.getContext('2d');
                                    new Chart(ctx, {
                                        type: 'bar',
                                        data: this.chartData,
                                        options: {{ json_encode($chartOptions) }}
                                    });
                                }
                            }" x-init="init()" wire:ignore>
                                <canvas x-ref="chartCanvasGuru"></canvas>
                            </div>
                        </x-card>
                    @else
                        <x-alert title="Data Grafik Belum Tersedia"
                            description="Grafik akan muncul setelah siswa di kelas Anda menyelesaikan ujian atau kuis."
                            icon="o-information-circle" />
                    @endif
                </div>

                <div class="lg:col-span-1 space-y-8">
                    <x-card title="Informasi Anda" icon="o-identification" shadow>
                        <div class="space-y-4">
                            <x-stat title="Kelas Diampu" :value="$this->kelasDiampu()->count()" icon="o-table-cells"
                                class="bg-sky-50 text-sky-800" />
                            <x-stat title="Total Siswa Anda" :value="$this->totalSiswaDiampu()" icon="o-users"
                                class="bg-purple-50 text-purple-800" />
                        </div>
                    </x-card>
                    <x-card title="Aksi Cepat" icon="o-bolt" shadow>
                        <div class="space-y-2">
                            <a href="{{ route('siswa.manage') }}" wire:navigate
                                class="block p-3 rounded-lg hover:bg-base-200 transition-colors">Kelola Siswa di
                                Kelas</a>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    @endif

    {{-- TAMPILAN SISWA --}}
    @if (auth()->user()->role === 'Siswa')
        <div x-init="gsap.from($el.children, { y: 20, opacity: 0, stagger: 0.1, duration: 0.5 })">
            <x-header separator>
                <div class="flex items-center space-x-4">
                    <x-avatar :image="route('files.user.foto', ['userId' => auth()->id()])" class="!w-16 !h-16" />
                    <div>
                        <h2 class="text-2xl font-bold">Hai, {{ Str::words(auth()->user()->nama, 1, '') }}!</h2>
                        <p class="text-gray-500">Ayo lanjutkan petualangan belajarmu!</p>
                    </div>
                </div>
            </x-header>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <x-card shadow>
                    <div class="flex items-center gap-4">
                        <x-icon name="o-star" class="w-10 h-10 text-yellow-500" />
                        <div>
                            <div class="text-gray-500">Rata-rata Skormu</div>
                            <div class="text-3xl font-bold">{{ round($this->rataRataSkor(), 2) }}</div>
                        </div>
                    </div>
                    <x-progress :value="$this->rataRataSkor()" class="progress-success mt-4" />
                </x-card>
                <x-card shadow>
                    <div class="flex items-center gap-4">
                        <x-icon name="o-bell-alert" class="w-10 h-10 text-red-500" />
                        <div>
                            <div class="text-gray-500">Tugas Baru</div>
                            <div class="text-3xl font-bold">{{ $this->tugasBelumDikerjakan()->count() }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-4">Selesaikan semua tugas untuk membuka pulau selanjutnya!</p>
                </x-card>
            </div>
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4">Ayo Kerjakan Ini!</h2>
                <div class="space-y-4">
                    @forelse($this->tugasBelumDikerjakan() as $tugas)
                        @php
                            $isUjian = $tugas instanceof \App\Models\Ujian;
                            $route = $isUjian
                                ? route('ujian.kerjakan', $tugas->slug ?? $tugas->id)
                                : route('kuis.kerjakan', $tugas->id);
                        @endphp
                        <a href="{{ $route }}" wire:navigate
                            class="block bg-base-100 p-4 rounded-lg shadow-md hover:shadow-xl hover:scale-[1.02] transition-all duration-300">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div @class([
                                        'p-3 rounded-full',
                                        'bg-blue-100' => $isUjian,
                                        'bg-green-100' => !$isUjian,
                                    ])>
                                        <x-icon :name="$isUjian ? 'o-academic-cap' : 'o-puzzle-piece'" @class([
                                            'w-6 h-6',
                                            'text-blue-500' => $isUjian,
                                            'text-green-500' => !$isUjian,
                                        ]) />
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $tugas->judul }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $isUjian ? 'Ujian Pilihan Ganda' : 'Kuis Menjodohkan' }}</div>
                                    </div>
                                </div>
                                <x-button label="Mulai" class="btn-primary btn-sm" />
                            </div>
                        </a>
                    @empty
                        <x-card class="text-center py-12">
                            <x-icon name="o-check-badge" class="w-16 h-16 mx-auto text-success" />
                            <h3 class="text-lg font-bold mt-4">Kerja Bagus!</h3>
                            <p class="text-gray-500">Semua tugas sudah selesai dikerjakan.</p>
                        </x-card>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
