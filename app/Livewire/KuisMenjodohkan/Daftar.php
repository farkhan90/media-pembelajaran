<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\HistoriKuis;
use App\Models\KuisMenjodohkan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Daftar extends Component
{
    public function render()
    {
        $user = Auth::user();

        $historiKuisSiswa = HistoriKuis::where('user_id', $user->id)
            ->select('kuis_id', DB::raw('MAX(skor_akhir) as skor_tertinggi'), DB::raw('COUNT(*) as jumlah_percobaan'))
            ->groupBy('kuis_id')
            ->get()
            // Jadikan ID kuis sebagai key untuk pencarian cepat di view
            ->keyBy('kuis_id');

        $ujians = KuisMenjodohkan::where('status', 'Published')->get();

        return view('livewire.kuis-menjodohkan.daftar', [
            'ujians' => $ujians,
            'historiKuisSiswa' => $historiKuisSiswa
        ]);
    }
}
