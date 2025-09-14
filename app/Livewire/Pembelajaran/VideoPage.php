<?php

namespace App\Livewire\Pembelajaran;

use App\Models\ProgresPulauSiswa;
use App\Models\SiswaPerkelas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class VideoPage extends Component
{
    public string $pulau;
    public string $judul;
    public string $videoFile;
    public ?string $pulauBerikutnya = null;

    public bool $sumateraSelesaiModal = false;
    // Properti untuk form refleksi di modal Sumatera
    #[Rule('required|string|min:100', message: 'Coba ceritakan sedikit lebih panjang ya!')]
    public string $jawabanPemantik = '';

    // "Mini-database" untuk konten video statis Anda
    protected array $dataPulau = [
        'sumatera' => [
            'judul' => 'Video 1: Keberagaman Indonesia',
            'file' => 'sumatera.m4v',
            'berikutnya' => 'jawa',
        ],
        'kalimantan' => [
            'judul' => 'Video 2: Harmoni dalam Keberagaman',
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
            // Siapkan data dasar yang akan disimpan
            $dataProgres = ['waktu_selesai' => now()];

            // Logika kondisional untuk Sumatera
            if ($this->pulau === 'sumatera') {
                $this->validateOnly('jawabanPemantik');
                // Tambahkan jawaban pemantik ke data
                $dataProgres['jawaban_pemantik'] = $this->jawabanPemantik;
            }

            // Gunakan updateOrCreate pada tabel progres yang benar
            ProgresPulauSiswa::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'nama_pulau' => $this->pulau,
                ],
                $dataProgres
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
