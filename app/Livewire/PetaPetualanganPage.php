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
    public array $progresSiswa = []; // Tetap array dari nama_pulau

    public function mount()
    {
        if (Auth::user()->role === 'Siswa') {
            // Kita tidak lagi butuh progres per pulau, tapi per langkah
            // Mari kita ambil langkah terakhir yang diselesaikan
            $langkahSelesai = ProgresLangkah::where('user_id', Auth::id())
                ->join('langkahs', 'progres_langkahs.langkah_id', '=', 'langkahs.id')
                ->orderBy('langkahs.urutan', 'desc')
                ->join('moduls', 'langkahs.modul_id', '=', 'moduls.id')
                ->orderBy('moduls.urutan', 'desc')
                ->first('moduls.nama_pulau');

            $this->progresSiswa['pulau_terakhir'] = $langkahSelesai?->nama_pulau;
        }
    }

    #[Computed]
    public function moduls()
    {
        // Ambil semua modul yang sudah di-publish, urutkan
        return Modul::where('is_published', true)->orderBy('urutan')->get();
    }

    #[Computed]
    public function pulauStatus(): array
    {
        $user = Auth::user();
        $urutanPulau = $this->moduls()->pluck('nama_pulau')->toArray();

        // Mode Jelajah Bebas
        if (in_array($user->role, ['Admin', 'Guru']) || $this->isPetualanganSelesai()) {
            return array_fill_keys($urutanPulau, 'terbuka');
        }

        // Mode Petualangan
        $status = [];
        $progresTerakhir = $this->progresSiswa['pulau_terakhir'] ?? null;
        $progresIndex = is_null($progresTerakhir) ? -1 : array_search($progresTerakhir, $urutanPulau);

        foreach ($urutanPulau as $index => $pulau) {
            if ($index <= $progresIndex) {
                $status[$pulau] = 'selesai';
            } elseif ($index === $progresIndex + 1) {
                $status[$pulau] = 'aktif';
            } else {
                $status[$pulau] = 'terkunci';
            }
        }

        return $status;
    }

    // Helper untuk cek apakah semua modul sudah selesai
    public function isPetualanganSelesai(): bool
    {
        $modulTerakhir = $this->moduls()->last();
        if (!$modulTerakhir) return false;

        $langkahTerakhir = $modulTerakhir->langkahs()->orderBy('urutan', 'desc')->first();
        if (!$langkahTerakhir) return false;

        return ProgresLangkah::where('user_id', Auth::id())
            ->where('langkah_id', $langkahTerakhir->id)
            ->exists();
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
