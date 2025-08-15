<?php

namespace App\Livewire\Pembelajaran;

use App\Models\HistoriUjian;
use App\Models\KuisMenjodohkan;
use App\Models\ProgresPulauSiswa;
use App\Models\Ujian;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class PenilaianRunner extends Component
{
    public string $pulau;
    public string $tahap;

    public ?Ujian $ujianPilgan = null;
    public ?KuisMenjodohkan $kuisMenjodohkan = null;
    public ?string $historiUjianId = null;

    public function mount(string $pulau, string $tahap = 'mulai', ?string $data = null)
    {
        $this->pulau = $pulau;
        $this->tahap = $tahap;

        $user = Auth::user();
        $kelasSiswa = $user->kelas->first();

        // Selalu muat data ujian/kuis yang relevan di awal
        if ($kelasSiswa) {
            $this->ujianPilgan = Ujian::where('kelas_id', $kelasSiswa->id)
                ->where('status', 'Published')
                ->latest()
                ->first();
            $this->kuisMenjodohkan = KuisMenjodohkan::where('kelas_id', $kelasSiswa->id)
                ->where('status', 'Published')
                ->latest()
                ->first();
        }

        // Tangani routing internal (jika pengguna me-refresh di tengah jalan)
        if ($tahap === 'pilgan' && $data) {
            $this->ujianPilgan = Ujian::findOrFail($data);
        } elseif ($tahap === 'menjodohkan' && $data) {
            $this->historiUjianId = $data;
        }
    }

    public function mulaiPenilaian()
    {
        if (!$this->ujianPilgan) {
            $this->dispatch('swal', ['title' => 'Oops!', 'text' => 'Ujian Pilihan Ganda belum siap.', 'icon' => 'warning']);
            return;
        }
        $this->tahap = 'pilgan';
    }

    // Menangkap event dari komponen Ujian/Pengerjaan
    #[On('ujianPilganSelesai')]
    public function handleUjianPilganSelesai($historiId)
    {
        // Pengaman: Jika objek kuis hilang, muat ulang.
        if (!$this->kuisMenjodohkan) {
            $user = Auth::user();
            $kelasSiswa = $user->kelas->first();
            if ($kelasSiswa) {
                $this->kuisMenjodohkan = KuisMenjodohkan::where('kelas_id', $kelasSiswa->id)
                    ->where('status', 'Published')
                    ->latest()
                    ->first();
            }
        }

        // Jika setelah dimuat ulang tetap tidak ada, beri tahu pengguna.
        if (!$this->kuisMenjodohkan) {
            $this->dispatch('swal', ['title' => 'Oops!', 'text' => 'Kuis Menjodohkan belum siap untuk kelasmu. Hubungi gurumu!', 'icon' => 'warning']);
            // Hentikan alur di sini agar tidak error
            $this->tahap = 'selesai'; // atau tahap error
            return;
        }

        $this->historiUjianId = $historiId;
        $this->tahap = 'menjodohkan'; // Lanjut ke tahap berikutnya
    }

    // Menangkap event dari komponen KuisMenjodohkan/Pengerjaan
    #[On('kuisMenjodohkanSelesai')]
    public function handleKuisMenjodohkanSelesai($historiKuisId, $skorKuis)
    {
        $user = Auth::user();
        $skorUjian = HistoriUjian::find($this->historiUjianId)->skor_akhir ?? 0;
        $skorAkumulasi = ($skorUjian + $skorKuis) / 2;

        ProgresPulauSiswa::updateOrCreate(
            ['user_id' => $user->id, 'nama_pulau' => 'papua'],
            [
                'waktu_selesai' => now(),
                'histori_ujian_id' => $this->historiUjianId,
                'histori_kuis_id' => $historiKuisId,
                'skor_akumulasi' => $skorAkumulasi,
            ]
        );

        $this->dispatch('kuis-telah-selesai', [
            'title' => 'Selamat, Petualangan Selesai!',
            'text' => 'Skor akhir gabunganmu adalah: ' . round($skorAkumulasi, 2),
            'icon' => 'success',
            'redirectUrl' => route('peta-petualangan')
        ]);

        $this->tahap = 'selesai';
    }

    public function render()
    {
        return view('livewire.pembelajaran.penilaian-runner');
    }
}
