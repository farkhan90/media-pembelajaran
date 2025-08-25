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
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    // Properti untuk Admin
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

    // Properti untuk Guru
    #[Computed]
    public function kelasDiampu()
    {
        if (Auth::user()->role !== 'Guru') return collect();
        return Kelas::where('guru_pengampu_id', Auth::id())->get();
    }

    #[Computed]
    public function totalSiswaDiampu()
    {
        if (Auth::user()->role !== 'Guru') return 0;
        $kelasIds = $this->kelasDiampu()->pluck('id');
        return User::where('role', 'Siswa')->whereHas('kelas', fn($q) => $q->whereIn('kelas.id', $kelasIds))->count();
    }

    #[Computed]
    public function chartSkorAntarSekolah()
    {
        if (Auth::user()->role !== 'Admin') return null;

        $sekolahs = Sekolah::with(['kelas.ujians.historiUjians', 'kelas.kuisMenjodohkan.historiKuis'])->get();

        $labels = [];
        $skorUjianAvg = [];
        $skorKuisAvg = [];

        foreach ($sekolahs as $sekolah) {
            $labels[] = Str::limit($sekolah->nama, 15); // Batasi panjang nama sekolah

            // Hitung rata-rata skor ujian
            $historiUjians = $sekolah->kelas->flatMap->ujians->flatMap->historiUjians;
            $skorUjianAvg[] = $historiUjians->avg('skor_akhir') ?? 0;

            // Hitung rata-rata skor kuis
            $historiKuis = $sekolah->kelas->flatMap->kuisMenjodohkan->flatMap->historiKuis;
            $skorKuisAvg[] = $historiKuis->avg('skor_akhir') ?? 0;
        }

        // Jangan tampilkan grafik jika tidak ada data
        if (empty($labels)) return null;

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Rata-rata Skor Kuis 1',
                    'data' => $skorUjianAvg,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Rata-rata Skor Kuis 2',
                    'data' => $skorKuisAvg,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Menyiapkan data grafik untuk Guru: Perbandingan skor rata-rata antar kelas yang diampu.
     */
    #[Computed]
    public function chartSkorAntarKelas()
    {
        if (Auth::user()->role !== 'Guru') return null;

        $kelases = Kelas::where('guru_pengampu_id', Auth::id())
            ->with(['ujians.historiUjians', 'kuisMenjodohkan.historiKuis'])
            ->get();

        $labels = [];
        $skorUjianAvg = [];
        $skorKuisAvg = [];

        foreach ($kelases as $kelas) {
            $labels[] = $kelas->nama;

            $historiUjians = $kelas->ujians->flatMap->historiUjians;
            $skorUjianAvg[] = $historiUjians->avg('skor_akhir') ?? 0;

            $historiKuis = $kelas->kuisMenjodohkan->flatMap->historiKuis;
            $skorKuisAvg[] = $historiKuis->avg('skor_akhir') ?? 0;
        }

        if (empty($labels)) return null;

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Rata-rata Skor Ujian',
                    'data' => $skorUjianAvg,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)', // Hijau
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Rata-rata Skor Kuis',
                    'data' => $skorKuisAvg,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)', // Kuning
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    // Properti untuk Siswa
    #[Computed]
    public function rataRataSkor()
    {
        if (Auth::user()->role !== 'Siswa') return 0;

        $skorUjian = HistoriUjian::where('user_id', Auth::id())->avg('skor_akhir');
        $skorKuis = HistoriKuis::where('user_id', Auth::id())->avg('skor_akhir');

        $totalSkor = ($skorUjian ?? 0) + ($skorKuis ?? 0);
        $jumlahJenis = ($skorUjian ? 1 : 0) + ($skorKuis ? 1 : 0);

        return $jumlahJenis > 0 ? $totalSkor / $jumlahJenis : 0;
    }

    #[Computed]
    public function tugasBelumDikerjakan()
    {
        if (Auth::user()->role !== 'Siswa') return collect();

        $user = Auth::user();
        $kelasSiswa = $user->kelas->first(); // Asumsi 1 siswa 1 kelas

        if (!$kelasSiswa) return collect();

        // Ujian yang belum dikerjakan
        $ujianDikerjakanIds = HistoriUjian::where('user_id', $user->id)->pluck('ujian_id');
        $ujianTersedia = Ujian::where('kelas_id', $kelasSiswa->id)
            ->where('status', 'Published')
            ->whereNotIn('id', $ujianDikerjakanIds)
            ->get();

        // Kuis yang belum dikerjakan
        $kuisDikerjakanIds = HistoriKuis::where('user_id', $user->id)->pluck('kuis_id');
        $kuisTersedia = KuisMenjodohkan::where('kelas_id', $kelasSiswa->id)
            ->where('status', 'Published')
            ->whereNotIn('id', $kuisDikerjakanIds)
            ->get();

        return $ujianTersedia->concat($kuisTersedia);
    }

    #[Computed]
    public function riwayatTerbaru()
    {
        if (Auth::user()->role !== 'Siswa') return collect();

        $ujian = HistoriUjian::where('user_id', Auth::id())->latest('waktu_selesai')->limit(2)->get();
        $kuis = HistoriKuis::where('user_id', Auth::id())->latest('waktu_selesai')->limit(2)->get();

        return $ujian->concat($kuis)->sortByDesc('waktu_selesai')->take(3);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
