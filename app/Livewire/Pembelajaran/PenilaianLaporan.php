<?php

namespace App\Livewire\Pembelajaran;

use App\Models\Langkah;
use App\Models\ProgresLangkah;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

// HAPUS SEMUA ATRIBUT LAYOUT DARI SINI
class PenilaianLaporan extends Component
{
    use WithPagination;

    // Properti ini akan di-pass dari ModulPlayer
    public Langkah $langkah;

    // Properti internal
    public array $headers;
    public Collection $podiumSiswa;

    // mount() sekarang hanya bertugas menyiapkan header
    public function mount()
    {
        $this->headers = [
            ['key' => 'peringkat', 'label' => 'Peringkat', 'class' => 'w-1'],
            ['key' => 'user.nama', 'label' => 'Nama Siswa'],
        ];

        if (Auth::user()->role === 'Admin') {
            $this->headers[] = ['key' => 'user.kelas_info', 'label' => 'Kelas / Sekolah'];
        }

        $this->headers = array_merge($this->headers, [
            ['key' => 'skor_akumulasi', 'label' => 'Skor Akhir', 'class' => 'w-24 text-center'],
            ['key' => 'waktu_selesai', 'label' => 'Waktu Selesai'],
        ]);
    }

    private function getAllPeringkatData(): Collection
    {
        $user = Auth::user();

        $query = ProgresLangkah::query()
            ->where('langkah_id', $this->langkah->id)
            ->whereNotNull('histori_ujian_id')
            ->with(['user.kelas.sekolah', 'historiUjian', 'historiKuis']);

        if ($user->role === 'Guru') {
            $siswaIds = User::whereHas('kelas', fn($q) => $q->whereIn('kelas.id', $user->kelasDiampu->pluck('id')))->pluck('users.id');
            $query->whereIn('user_id', $siswaIds);
        }

        return $query->get()->map(function ($progres) {
            $skorUjian = $progres->historiUjian?->skor_akhir ?? 0;
            $skorKuis = $progres->historiKuis?->skor_akhir ?? 0;
            $progres->skor_akumulasi = ($skorUjian + $skorKuis) / 2;
            return $progres;
        })->sortByDesc('skor_akumulasi')->values();
    }

    public function render()
    {
        $allPeringkat = $this->getAllPeringkatData();

        $this->podiumSiswa = $allPeringkat->take(3);
        $sisaPeringkat = $allPeringkat->slice(3);

        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $perPage = 15;
        $peringkatTabel = new LengthAwarePaginator(
            $sisaPeringkat->forPage($currentPage, $perPage),
            $sisaPeringkat->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return view('livewire.pembelajaran.penilaian-laporan', [
            'peringkatTabel' => $peringkatTabel
        ]);
    }
}
