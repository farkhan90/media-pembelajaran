<?php

namespace App\Livewire\Pembelajaran;

use App\Models\ProgresPulauSiswa;
use App\Models\SiswaPerkelas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class MateriPage extends Component
{
    public string $pulau;
    public string $judul;
    public ?string $pulauBerikutnya = 'kalimantan'; // Hardcode untuk Jawa

    // Urutan pulau untuk validasi progres
    protected array $urutanPulau = ['sumatera', 'jawa', 'kalimantan', 'sulawesi', 'papua'];

    /**
     * Dijalankan saat komponen dimuat.
     * Melakukan otorisasi untuk memastikan siswa berada di pulau yang benar.
     */
    public function mount(string $pulau)
    {
        // Untuk saat ini, kita asumsikan halaman ini hanya untuk 'jawa'
        if ($pulau !== 'jawa') {
            abort(404);
        }

        $this->pulau = $pulau;
        $this->judul = "Bersatu Dalam Keberagaman";

        $user = Auth::user();

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
    }

    /**
     * Menandai pulau ini sebagai selesai untuk siswa.
     */
    public function tandaiSelesai()
    {
        $user = Auth::user();

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
        // Karena konten statis, kita bisa langsung merender view
        return view('livewire.pembelajaran.materi-page');
    }
}
