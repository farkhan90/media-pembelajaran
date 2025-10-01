<?php

namespace App\Livewire\Pembelajaran;

use App\Models\HistoriUjian;
use App\Models\KuisMenjodohkan;
use App\Models\ProgresLangkah;
use App\Models\Ujian;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class PenilaianRunner extends Component
{
    public string $pulau;
    public string $langkahId;
    public string $ujianPilganId;
    public string $kuisMenjodohkanId;

    public string $tahap = 'mulai';
    public ?Ujian $ujianPilgan = null;
    public ?KuisMenjodohkan $kuisMenjodohkan = null;
    public ?string $historiUjianId = null;

    public function mount(string $pulau, string $langkahId, string $ujianPilganId, string $kuisMenjodohkanId)
    {
        $this->pulau = $pulau;
        $this->langkahId = $langkahId;
        $this->ujianPilganId = $ujianPilganId;
        $this->kuisMenjodohkanId = $kuisMenjodohkanId;
    }

    public function mulaiPenilaian()
    {
        $this->ujianPilgan = Ujian::find($this->ujianPilganId);
        if (!$this->ujianPilgan) {
            $this->dispatch('swal', ['title' => 'Oops!', 'text' => 'Ujian Pilihan Ganda untuk penilaian ini belum disiapkan.', 'icon' => 'error']);
            return;
        }
        $this->tahap = 'pilgan';
    }

    #[On('ujianPilganSelesai')]
    public function handleUjianPilganSelesai($historiId)
    {
        $this->historiUjianId = $historiId;
        $this->kuisMenjodohkan = KuisMenjodohkan::find($this->kuisMenjodohkanId);
        if (!$this->kuisMenjodohkan) {
            $this->dispatch('swal', ['title' => 'Oops!', 'text' => 'Kuis Menjodohkan untuk penilaian ini belum disiapkan.', 'icon' => 'error']);
            return;
        }
        $this->tahap = 'menjodohkan';
    }

    #[On('kuisMenjodohkanSelesai')]
    public function handleKuisMenjodohkanSelesai($historiKuisId, $skorKuis)
    {
        $user = Auth::user();
        $skorUjian = HistoriUjian::find($this->historiUjianId)->skor_akhir ?? 0;
        $skorAkumulasi = ($skorUjian + $skorKuis) / 2;

        // Simpan progres ke tabel progres_langkahs, tandai langkah ini selesai
        ProgresLangkah::updateOrCreate(
            ['user_id' => $user->id, 'langkah_id' => $this->langkahId],
            [
                'status' => 'selesai',
                'waktu_selesai' => now(),
                'histori_ujian_id' => $this->historiUjianId,
                'histori_kuis_id' => $historiKuisId,
            ]
        );

        // Tampilkan hasil dan arahkan ke halaman selamat/peta
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
