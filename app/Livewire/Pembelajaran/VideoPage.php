<?php

namespace App\Livewire\Pembelajaran;

use App\Models\ProgresPulauSiswa;
use App\Models\SiswaPerkelas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class VideoPage extends Component
{
    public string $pulau;
    public string $judul;
    public string $videoFile;
    public ?string $pulauBerikutnya = null;

    public bool $sumateraSelesaiModal = false;

    // "Mini-database" untuk konten video statis Anda
    protected array $dataPulau = [
        'sumatera' => [
            'judul' => 'Video 1: Petualangan di Kerajaan Sriwijaya',
            'file' => 'sumatera.mp4',
            'berikutnya' => 'jawa',
        ],
        'kalimantan' => [
            'judul' => 'Video 2: Misteri Kerajaan Kutai',
            'file' => 'kalimantan.m4v',
            'berikutnya' => 'sulawesi',
        ],
    ];

    // Urutan pulau untuk validasi progres
    protected array $urutanPulau = ['sumatera', 'jawa', 'kalimantan', 'sulawesi', 'papua'];

    /**
     * Dijalankan saat komponen dimuat.
     * Mengambil data, dan melakukan otorisasi.
     */
    public function mount(string $pulau)
    {
        $user = Auth::user();
        $this->pulau = $pulau;

        // Validasi: Pastikan data untuk pulau ini ada di config kita
        if (!isset($this->dataPulau[$pulau])) {
            abort(404, 'Video untuk pulau ini tidak ditemukan.');
        }

        // Otorisasi: Siswa hanya bisa mengakses pulau yang aktif
        if ($user->role === 'Siswa') {
            $progresSiswa = ProgresPulauSiswa::where('user_id', $user->id)->pluck('nama_pulau')->toArray();

            // Cek dulu apakah siswa sudah tamat
            $sudahTamat = in_array('papua', $progresSiswa);

            if (!$sudahTamat) {
                // Jika belum tamat, jalankan logika otorisasi linear
                $progresTerakhir = $this->getProgresTerakhir($progresSiswa);
                $progresIndex = is_null($progresTerakhir) ? -1 : array_search($progresTerakhir, $this->urutanPulau);
                $pulauAktif = $this->urutanPulau[$progresIndex + 1] ?? null;

                if ($pulau !== $pulauAktif) {
                    session()->flash('pesan_error', 'Selesaikan pulau sebelumnya dulu ya!');
                    $this->redirect(route('peta-petualangan'), navigate: true);
                    return;
                }
            }
            // Jika sudah tamat, tidak ada pengecekan lebih lanjut, akses diizinkan.
        }

        // Muat data untuk pulau yang diminta
        $this->judul = $this->dataPulau[$pulau]['judul'];
        $this->videoFile = $this->dataPulau[$pulau]['file'];
        $this->pulauBerikutnya = $this->dataPulau[$pulau]['berikutnya'];
    }

    /**
     * Menandai pulau ini sebagai selesai untuk siswa.
     */
    public function tandaiSelesai()
    {
        $user = Auth::user();

        // Simpan progres hanya untuk siswa
        if ($user->role === 'Siswa') {

            ProgresPulauSiswa::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'nama_pulau' => $this->pulau,
                ],
                [
                    'waktu_selesai' => now(),
                ]
            );
        }

        // Redirect kembali ke peta dengan navigasi SPA
        return $this->redirect(route('peta-petualangan'), navigate: true);
    }

    /**
     * Helper untuk mendapatkan item terakhir dari progres
     */
    private function getProgresTerakhir(array $progres): ?string
    {
        $lastProgress = null;
        foreach ($this->urutanPulau as $pulau) {
            if (in_array($pulau, $progres)) {
                $lastProgress = $pulau;
            } else {
                break;
            }
        }
        return $lastProgress;
    }


    public function render()
    {
        return view('livewire.pembelajaran.video-page');
    }
}
