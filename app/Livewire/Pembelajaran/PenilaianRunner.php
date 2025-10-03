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
    // Properti yang diterima dari ModulPlayer
    public string $langkahId;
    public ?string $ujianPilganId;
    public ?string $kuisMenjodohkanId;

    public string $tahap = 'mulai'; // State: mulai, pilgan, menjodohkan, selesai

    // Properti yang di-load secara dinamis
    public ?Ujian $ujianPilgan = null;
    public ?KuisMenjodohkan $kuisMenjodohkan = null;
    public ?string $historiUjianId = null;

    public function mount()
    {
        // Pengaman: Jika ID tidak ada, ubah tahap menjadi 'error'
        if (!$this->ujianPilganId || !$this->kuisMenjodohkanId) {
            $this->tahap = 'error';
        }
    }

    public function mulaiPenilaian()
    {
        $this->ujianPilgan = Ujian::find($this->ujianPilganId);
        if (!$this->ujianPilgan) {
            $this->tahap = 'error';
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
            $this->tahap = 'error';
            return;
        }
        $this->tahap = 'menjodohkan';
    }

    #[On('kuisMenjodohkanSelesai')]
    public function handleKuisMenjodohkanSelesai($historiKuisId, $skorKuis)
    {
        $user = Auth::user();

        // Pastikan historiUjianId ada sebelum menghitung
        if (!$this->historiUjianId) {
            $this->tahap = 'error';
            $this->dispatch('swal', ['title' => 'Error', 'text' => 'Sesi ujian pilihan ganda tidak ditemukan.', 'icon' => 'error']);
            return;
        }

        $skorUjian = HistoriUjian::find($this->historiUjianId)->skor_akhir ?? 0;
        $skorAkumulasi = ($skorUjian + $skorKuis) / 2;

        // Simpan progres ke tabel progres_langkahs
        ProgresLangkah::updateOrCreate(
            ['user_id' => $user->id, 'langkah_id' => $this->langkahId],
            [
                'status' => 'selesai',
                'waktu_selesai' => now(),
                'histori_ujian_id' => $this->historiUjianId,
                'histori_kuis_id' => $historiKuisId,
                'jawaban_teks' => json_encode(['skor_akumulasi' => $skorAkumulasi]) // Simpan skor di sini
            ]
        );

        // Beri tahu ModulPlayer bahwa langkah ini sudah selesai
        $this->dispatch('langkah-penilaian-selesai', skor: $skorAkumulasi)->to(ModulPlayer::class);

        $this->tahap = 'selesai';
    }

    public function render()
    {
        return view('livewire.pembelajaran.penilaian-runner');
    }
}
