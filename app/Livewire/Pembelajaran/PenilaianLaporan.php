<?php

namespace App\Livewire\Pembelajaran;

use App\Models\ProgresPulauSiswa;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.guest')]
class PenilaianLaporan extends Component
{
    use WithPagination;

    public string $pulau;
    public array $headers;

    public Collection $podiumSiswa;

    public function mount(string $pulau)
    {
        $this->pulau = $pulau;

        $this->headers = [
            ['key' => 'peringkat', 'label' => 'Peringkat', 'class' => 'w-1'],
            ['key' => 'user.nama', 'label' => 'Nama Siswa'],
        ];

        // Tambahkan kolom sekolah & kelas HANYA untuk Admin
        if (Auth::user()->role === 'Admin') {
            $this->headers[] = ['key' => 'user.kelas_info', 'label' => 'Kelas / Sekolah'];
        }

        // Tambahkan sisa kolom
        $this->headers = array_merge($this->headers, [
            ['key' => 'skor_akumulasi', 'label' => 'Skor Akhir', 'class' => 'w-24 text-center'],
            ['key' => 'waktu_selesai', 'label' => 'Waktu Selesai'],
        ]);
    }

    #[Computed]
    public function getPeringkatData()
    {
        $user = Auth::user();
        $query = ProgresPulauSiswa::query()
            ->where('nama_pulau', $this->pulau)
            ->whereNotNull('skor_akumulasi')
            ->with(['user.kelas.sekolah']);

        if ($user->role === 'Guru') {
            $kelasDiampuIds = $user->kelasDiampu->pluck('id');
            $query->whereIn('kelas_id', $kelasDiampuIds);
        }

        // Ambil SEMUA data, jangan paginate di sini
        return $query->orderBy('skor_akumulasi', 'desc')
            ->orderBy('waktu_selesai', 'asc')
            ->get();
    }

    public function setupPeringkat()
    {
        $allPeringkat = $this->getPeringkatData();

        // Ambil 3 teratas untuk podium
        $this->podiumSiswa = $allPeringkat->take(3);

        // Ambil sisanya untuk tabel dan buat paginasi manual
        $sisaPeringkat = $allPeringkat->slice(3);

        // Dapatkan halaman saat ini dari query string
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15; // Berapa item per halaman tabel

        // Buat instance paginator secara manual
        return new LengthAwarePaginator(
            $sisaPeringkat->forPage($currentPage, $perPage), // Item untuk halaman ini
            $sisaPeringkat->count(), // Total item
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()] // Path URL
        );
    }

    #[Computed]
    public function peringkatSiswa()
    {
        $user = Auth::user();

        $query = ProgresPulauSiswa::query()
            ->where('nama_pulau', $this->pulau)
            ->whereNotNull('skor_akumulasi')
            ->with(['user.kelas.sekolah']);

        if ($user->role === 'Guru') {
            $kelasDiampuIds = $user->kelasDiampu->pluck('id');
            $query->whereIn('kelas_id', $kelasDiampuIds);
        }

        return $query->orderBy('skor_akumulasi', 'desc')
            ->orderBy('waktu_selesai', 'asc')
            ->paginate(20);
    }

    public function render()
    {
        $peringkatTabel = $this->setupPeringkat();

        return view('livewire.pembelajaran.penilaian-laporan', [
            'peringkatTabel' => $peringkatTabel // Kirim data tabel ke view
        ]);
    }
}
