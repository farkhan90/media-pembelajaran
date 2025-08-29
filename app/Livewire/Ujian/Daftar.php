<?php

namespace App\Livewire\Ujian;

use App\Models\HistoriUjian;
use App\Models\Ujian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Daftar extends Component
{
    // app/Livewire/Ujian/Daftar.php

    public function render()
    {
        $user = Auth::user();

        $historiUjianSiswa = HistoriUjian::where('user_id', $user->id)
            ->select('ujian_id', DB::raw('MAX(skor_akhir) as skor_tertinggi'), DB::raw('COUNT(*) as jumlah_percobaan'))
            ->groupBy('ujian_id')
            ->get()
            ->keyBy('ujian_id');

        $ujians = Ujian::where('status', 'Published')->get();

        return view('livewire.ujian.daftar', [
            'ujians' => $ujians,
            'historiUjianSiswa' => $historiUjianSiswa
        ]);
    }
}
