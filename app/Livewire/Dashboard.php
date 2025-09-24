<?php

namespace App\Livewire;

use App\Models\HistoriKuis;
use App\Models\HistoriUjian;
use App\Models\Kelas;
use App\Models\KuisMenjodohkan;
use App\Models\Sekolah;
use App\Models\Ujian;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Str;

#[Title('Dashboard')]
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public array $chartOptions = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'max' => 100
            ]
        ]
    ];
    // DATA UNTUK ADMIN
    #[Computed(cache: true)]
    public function totalAdmin()
    {
        return User::where('role', 'Admin')->count();
    }
    #[Computed(cache: true)]
    public function totalGuru()
    {
        return User::where('role', 'Guru')->count();
    }
    #[Computed(cache: true)]
    public function totalSiswa()
    {
        return User::where('role', 'Siswa')->count();
    }
    #[Computed(cache: true)]
    public function totalSekolah()
    {
        return Sekolah::count();
    }

    #[Computed]
    public function peringkatSekolahTeratas()
    {
        if (Auth::user()->role !== 'Admin') return collect();

        // Ambil rata-rata skor gabungan per sekolah, urutkan, dan ambil 5 teratas
        return Sekolah::withCount('kelas')
            ->get()
            ->map(function ($sekolah) {
                $historiUjian = HistoriUjian::whereHas('user.kelas', fn($q) => $q->where('sekolah_id', $sekolah->id))->pluck('skor_akhir');
                $historiKuis = HistoriKuis::whereHas('user.kelas', fn($q) => $q->where('sekolah_id', $sekolah->id))->pluck('skor_akhir');

                $skorGabungan = $historiUjian->concat($historiKuis);

                $sekolah->skor_rata_rata = $skorGabungan->isNotEmpty() ? $skorGabungan->avg() : 0;
                return $sekolah;
            })
            ->sortByDesc('skor_rata_rata')
            ->take(5);
    }

    // DATA UNTUK GURU
    #[Computed]
    public function kelasDiampu()
    {
        if (Auth::user()->role !== 'Guru') return collect();
        return Auth::user()->kelasDiampu()->with('sekolah')->get();
    }

    #[Computed]
    public function totalSiswaDiampu()
    {
        if (Auth::user()->role !== 'Guru') return 0;
        $kelasIds = $this->kelasDiampu()->pluck('id');
        return DB::table('siswa_perkelas')->whereIn('kelas_id', $kelasIds)->count();
    }

    #[Computed]
    public function performaKelas()
    {
        if (Auth::user()->role !== 'Guru') return collect();

        return $this->kelasDiampu()->map(function ($kelas) {
            $siswaIds = $kelas->siswa()->pluck('users.id');

            if ($siswaIds->isEmpty()) {
                $kelas->skor_rata_rata = 0;
                return $kelas;
            }

            $historiUjian = HistoriUjian::whereIn('user_id', $siswaIds)->pluck('skor_akhir');
            $historiKuis = HistoriKuis::whereIn('user_id', $siswaIds)->pluck('skor_akhir');

            $skorGabungan = $historiUjian->concat($historiKuis);

            $kelas->skor_rata_rata = $skorGabungan->isNotEmpty() ? $skorGabungan->avg() : 0;
            return $kelas;
        })->sortByDesc('skor_rata_rata');
    }

    #[Computed]
    public function chartSkorAntarSekolah()
    {
        if (Auth::user()->role !== 'Admin') return null;

        $avgUjianPerSekolah = DB::table('histori_ujians')
            ->select('sekolahs.nama', DB::raw('AVG(histori_ujians.skor_akhir) as avg_skor'))
            ->join('users', 'histori_ujians.user_id', '=', 'users.id')
            ->join('siswa_perkelas', 'users.id', '=', 'siswa_perkelas.user_id')
            ->join('kelas', 'siswa_perkelas.kelas_id', '=', 'kelas.id')
            ->join('sekolahs', 'kelas.sekolah_id', '=', 'sekolahs.id')
            ->groupBy('sekolahs.id', 'sekolahs.nama')
            ->get()->keyBy('nama');

        $avgKuisPerSekolah = DB::table('histori_kuis')
            ->select('sekolahs.nama', DB::raw('AVG(histori_kuis.skor_akhir) as avg_skor'))
            ->join('users', 'histori_kuis.user_id', '=', 'users.id')
            ->join('siswa_perkelas', 'users.id', '=', 'siswa_perkelas.user_id')
            ->join('kelas', 'siswa_perkelas.kelas_id', '=', 'kelas.id')
            ->join('sekolahs', 'kelas.sekolah_id', '=', 'sekolahs.id')
            ->groupBy('sekolahs.id', 'sekolahs.nama')
            ->get()->keyBy('nama');

        $sekolahs = \App\Models\Sekolah::orderBy('nama')->get();
        if ($sekolahs->isEmpty()) return null;

        $labels = $sekolahs->pluck('nama')->map(fn($nama) => Str::limit($nama, 15))->toArray();
        $skorUjianAvg = $sekolahs->map(fn($s) => $avgUjianPerSekolah->get($s->nama)?->avg_skor ?? 0)->toArray();
        $skorKuisAvg = $sekolahs->map(fn($s) => $avgKuisPerSekolah->get($s->nama)?->avg_skor ?? 0)->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Rata-rata Skor Ujian', 'data' => $skorUjianAvg, 'backgroundColor' => 'rgba(59, 130, 246, 0.5)'],
                ['label' => 'Rata-rata Skor Kuis', 'data' => $skorKuisAvg, 'backgroundColor' => 'rgba(239, 68, 68, 0.5)']
            ]
        ];
    }

    // =======================================================
    //     PASTIKAN COMPUTED PROPERTY INI JUGA ADA DAN AKTIF
    // =======================================================
    #[Computed]
    public function chartSkorAntarKelas()
    {
        if (Auth::user()->role !== 'Guru') return null;
        $kelases = $this->kelasDiampu();
        if ($kelases->isEmpty()) return null;

        $allSiswaIds = DB::table('siswa_perkelas')->whereIn('kelas_id', $kelases->pluck('id'))->pluck('user_id');
        if ($allSiswaIds->isEmpty()) return null;

        $avgUjianPerKelas = DB::table('histori_ujians')
            ->select('kelas.nama', DB::raw('AVG(histori_ujians.skor_akhir) as avg_skor'))
            ->join('users', 'histori_ujians.user_id', '=', 'users.id')
            ->join('siswa_perkelas', 'users.id', '=', 'siswa_perkelas.user_id')
            ->join('kelas', 'siswa_perkelas.kelas_id', '=', 'kelas.id')
            ->whereIn('siswa_perkelas.user_id', $allSiswaIds)
            ->groupBy('kelas.id', 'kelas.nama')
            ->get()->keyBy('nama');

        $avgKuisPerKelas = DB::table('histori_kuis')
            ->select('kelas.nama', DB::raw('AVG(histori_kuis.skor_akhir) as avg_skor'))
            ->join('users', 'histori_kuis.user_id', '=', 'users.id')
            ->join('siswa_perkelas', 'users.id', '=', 'siswa_perkelas.user_id')
            ->join('kelas', 'siswa_perkelas.kelas_id', '=', 'kelas.id')
            ->whereIn('siswa_perkelas.user_id', $allSiswaIds)
            ->groupBy('kelas.id', 'kelas.nama')
            ->get()->keyBy('nama');

        $labels = $kelases->pluck('nama')->toArray();
        $skorUjianAvg = $kelases->map(fn($k) => $avgUjianPerKelas->get($k->nama)?->avg_skor ?? 0)->toArray();
        $skorKuisAvg = $kelases->map(fn($k) => $avgKuisPerKelas->get($k->nama)?->avg_skor ?? 0)->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Rata-rata Skor Ujian', 'data' => $skorUjianAvg, 'backgroundColor' => 'rgba(34, 197, 94, 0.5)'],
                ['label' => 'Rata-rata Skor Kuis', 'data' => $skorKuisAvg, 'backgroundColor' => 'rgba(245, 158, 11, 0.5)']
            ]
        ];
    }

    // DATA UNTUK SISWA
    #[Computed]
    public function rataRataSkor()
    {
        if (Auth::user()->role !== 'Siswa') return 0;
        $avgUjian = HistoriUjian::where('user_id', Auth::id())->avg('skor_akhir');
        $avgKuis = HistoriKuis::where('user_id', Auth::id())->avg('skor_akhir');
        $scores = collect([$avgUjian, $avgKuis])->filter();
        return $scores->isNotEmpty() ? $scores->avg() : 0;
    }

    #[Computed]
    public function tugasBelumDikerjakan()
    {
        $user = Auth::user();
        if ($user->role !== 'Siswa') return collect();

        $ujianDikerjakanIds = HistoriUjian::where('user_id', $user->id)->pluck('ujian_id');
        $ujianTersedia = Ujian::where('status', 'Published')->whereNotIn('id', $ujianDikerjakanIds)->get();

        $kuisDikerjakanIds = HistoriKuis::where('user_id', $user->id)->pluck('kuis_id');
        $kuisTersedia = KuisMenjodohkan::where('status', 'Published')->whereNotIn('id', $kuisDikerjakanIds)->get();

        return $ujianTersedia->concat($kuisTersedia);
    }

    #[Computed]
    public function riwayatTerbaru()
    {
        if (Auth::user()->role !== 'Siswa') return collect();
        $ujian = HistoriUjian::where('user_id', Auth::id())->latest('waktu_selesai')->limit(3)->get();
        $kuis = HistoriKuis::where('user_id', Auth::id())->latest('waktu_selesai')->limit(3)->get();
        return $ujian->concat($kuis)->sortByDesc('waktu_selesai')->take(3);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
