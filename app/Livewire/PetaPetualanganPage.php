<?php

namespace App\Livewire;

use App\Models\Modul;
use App\Models\ProgresLangkah;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class PetaPetualanganPage extends Component
{
    // Properti ini akan menyimpan NAMA_PULAU dari modul terakhir yang TUNTAS
    public ?string $progresModulTuntasTerakhir = null;

    /**
     * Dijalankan saat komponen dimuat.
     * Menganalisis progres siswa untuk menentukan modul terakhir yang tuntas.
     */
    public function mount()
    {
        if (Auth::user()->role === 'Siswa') {
            $user = Auth::user();

            // Ambil semua modul yang harus dikerjakan, diurutkan
            $semuaModul = Modul::where('is_published', true)->orderBy('urutan')->with('langkahs')->get();

            // Ambil semua ID langkah yang sudah diselesaikan siswa
            $langkahSelesaiIds = ProgresLangkah::where('user_id', $user->id)->pluck('langkah_id');

            // Loop melalui setiap modul untuk memeriksa status penyelesaiannya
            foreach ($semuaModul as $modul) {
                // Ambil semua ID langkah yang ada di modul ini
                $langkahDiModulIds = $modul->langkahs->pluck('id');

                if ($langkahDiModulIds->isEmpty()) {
                    continue; // Lewati modul yang tidak punya langkah
                }

                // Cek apakah semua langkah di modul ini sudah diselesaikan
                $langkahBelumSelesai = $langkahDiModulIds->diff($langkahSelesaiIds);

                if ($langkahBelumSelesai->isEmpty()) {
                    // Jika tuntas, catat sebagai progres terakhir sejauh ini
                    $this->progresModulTuntasTerakhir = $modul->nama_pulau;
                } else {
                    // Begitu kita menemukan modul pertama yang belum tuntas,
                    // kita berhenti. Progres terakhir adalah modul sebelumnya.
                    break;
                }
            }
        }
    }

    #[Computed]
    public function moduls()
    {
        // Ambil semua modul yang sudah di-publish, urutkan
        return Modul::where('is_published', true)->orderBy('urutan')->get();
    }

    #[Computed]
    public function modulStatus(): array
    {
        $user = Auth::user();
        // 1. Dapatkan daftar nama modul secara dinamis dari database
        $urutanModul = $this->moduls()->pluck('nama_pulau')->toArray();

        // Mode Jelajah Bebas untuk Admin, Guru, atau siswa yang sudah tamat
        if (in_array($user->role, ['Admin', 'Guru']) || $this->isPetualanganSelesai()) {
            return array_fill_keys($urutanModul, 'terbuka');
        }

        $status = [];
        $progresIndex = is_null($this->progresModulTuntasTerakhir) ? -1 : array_search($this->progresModulTuntasTerakhir, $urutanModul);

        foreach ($urutanModul as $index => $namaModul) {
            if ($index <= $progresIndex) {
                $status[$namaModul] = 'terbuka';
            } elseif ($index === $progresIndex + 1) {
                $status[$namaModul] = 'aktif';
            } else {
                $status[$namaModul] = 'terkunci';
            }
        }

        return $status;
    }

    // Helper untuk cek apakah semua modul sudah selesai
    public function isPetualanganSelesai(): bool
    {
        $modulTerakhir = $this->moduls()->last();
        if (!$modulTerakhir) return false;

        // Periksa apakah modul terakhir ada di dalam daftar progres
        $progresSiswa = ProgresLangkah::where('user_id', Auth::id())
            ->join('langkahs', 'progres_langkahs.langkah_id', '=', 'langkahs.id')
            ->where('langkahs.modul_id', $modulTerakhir->id)
            ->pluck('langkahs.urutan');

        $langkahTerakhirUrutan = $modulTerakhir->langkahs()->max('urutan');

        // Selesai jika jumlah progres di modul terakhir sama dengan jumlah langkah total,
        // DAN langkah dengan urutan tertinggi sudah diselesaikan.
        return $progresSiswa->count() === $modulTerakhir->langkahs()->count() && $progresSiswa->contains($langkahTerakhirUrutan);
    }

    public function getLinkForPulau(string $namaPulau): string
    {
        // Laravel akan otomatis menggunakan 'nama_pulau'
        return route('pembelajaran.modul.player', ['modul' => $namaPulau]);
    }

    public function render()
    {
        return view('livewire.peta-petualangan-page');
    }
}
