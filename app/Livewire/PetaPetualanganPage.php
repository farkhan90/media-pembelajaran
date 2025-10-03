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
    public ?string $progresModulTerakhir = null; // Tetap array dari nama_pulau

    public function mount()
    {
        if (Auth::user()->role === 'Siswa') {
            // Kita tidak lagi butuh progres per pulau, tapi per langkah
            // Mari kita ambil langkah terakhir yang diselesaikan
            $progresTerakhir = ProgresLangkah::where('user_id', Auth::id())
                ->join('langkahs', 'progres_langkahs.langkah_id', '=', 'langkahs.id')
                ->join('moduls', 'langkahs.modul_id', '=', 'moduls.id')
                ->orderBy('moduls.urutan', 'desc')
                ->orderBy('langkahs.urutan', 'desc')
                ->select('moduls.nama_pulau') // Hanya pilih kolom yang kita butuhkan
                ->first();

            // Gunakan nullsafe operator (?) untuk mengakses properti dengan aman.
            // Jika $progresTerakhir adalah null, $this->progresModulTerakhir juga akan menjadi null.
            $this->progresModulTerakhir = $progresTerakhir?->nama_pulau;
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
        $urutanModulDariDb = $this->moduls()->pluck('nama_pulau')->toArray();

        // Mode Jelajah Bebas untuk Admin, Guru, atau siswa yang sudah tamat
        if (in_array($user->role, ['Admin', 'Guru']) || $this->isPetualanganSelesai()) {
            // Gunakan daftar dinamis ini
            return array_fill_keys($urutanModulDariDb, 'terbuka');
        }

        // Mode Petualangan Siswa
        $status = [];
        $progresTerakhir = $this->progresModulTerakhir; // Properti ini sudah diisi di mount()

        // Gunakan daftar dinamis ini untuk mencari
        $progresIndex = is_null($progresTerakhir) ? -1 : array_search($progresTerakhir, $urutanModulDariDb);

        // Loop pada daftar dinamis ini
        foreach ($urutanModulDariDb as $index => $namaModul) {
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

    public function getLinkForPulau(string $modulId): string
    {
        return route('pembelajaran.modul.player', ['modul' => $modulId]);
    }

    public function render()
    {
        return view('livewire.peta-petualangan-page');
    }
}
