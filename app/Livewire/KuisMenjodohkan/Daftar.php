<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\HistoriKuis;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Daftar extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Eager load relasi kelas dan kuis
        $user->load('kelas.kuisMenjodohkan');
        $kelasSiswa = $user->kelas;

        // Query agregat untuk mengambil semua histori kuis siswa,
        // menghitung skor tertinggi dan jumlah percobaan untuk setiap kuis.
        $historiKuisSiswa = HistoriKuis::where('user_id', $user->id)
            ->select('kuis_id', DB::raw('MAX(skor_akhir) as skor_tertinggi'), DB::raw('COUNT(*) as jumlah_percobaan'))
            ->groupBy('kuis_id')
            ->get()
            // Jadikan ID kuis sebagai key untuk pencarian cepat di view
            ->keyBy('kuis_id');

        return view('livewire.kuis-menjodohkan.daftar', [
            'kelases' => $kelasSiswa,
            'historiKuisSiswa' => $historiKuisSiswa
        ]);
    }
}
