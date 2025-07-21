<?php

namespace App\Livewire\Ujian;

use App\Models\HistoriUjian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Daftar extends Component
{
    // app/Livewire/Ujian/Daftar.php

    public function render()
    {
        $user = auth()->user();

        // Pastikan relasi kelas dimuat dengan benar
        $user->load('kelas');
        $kelasSiswa = $user->kelas;

        // Ambil SEMUA histori ujian milik siswa. Titik.
        // Kita tidak perlu mem-pre-filter berdasarkan kelas di sini, karena histori sudah pasti
        // terkait dengan ujian yang ada di kelas siswa. Ini lebih sederhana dan lebih andal.
        $historiUjianSiswa = HistoriUjian::where('user_id', $user->id)
            ->get()
            // Jadikan ID ujian sebagai key untuk pencarian cepat di view
            ->keyBy('ujian_id');

        // Jika ingin menampilkan skor tertinggi, kita perlu query yang sedikit berbeda.
        // Mari kita gabungkan:
        $historiUjianSiswa = HistoriUjian::where('user_id', $user->id)
            ->select('ujian_id', DB::raw('MAX(skor_akhir) as skor_tertinggi'), DB::raw('COUNT(*) as jumlah_percobaan'))
            ->groupBy('ujian_id')
            ->get()
            ->keyBy('ujian_id');

        return view('livewire.ujian.daftar', [
            'kelases' => $kelasSiswa,
            'historiUjianSiswa' => $historiUjianSiswa
        ]);
    }
}
