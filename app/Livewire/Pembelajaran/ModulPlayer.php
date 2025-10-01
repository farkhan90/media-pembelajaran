<?php

namespace App\Livewire\Pembelajaran;

use App\Models\Langkah;
use App\Models\Modul;
use App\Models\ProgresLangkah;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.guest')]
class ModulPlayer extends Component
{
    public Modul $modul;
    public ?Langkah $langkahAktif = null;
    public ?int $langkahAktifIndex = null;
    public Collection $langkahSelesaiIds;
    public bool $semuaSelesai = false;

    public bool $bisaLanjut = false;

    // Properti untuk menampung jawaban dari viewer soal esai
    public string $jawabanEsai = '';

    public function mount(Modul $modul)
    {
        $this->modul = $modul->load('langkahs'); // Eager load langkah-langkah
        $this->tentukanLangkahAktif();
    }

    /**
     * Menentukan langkah mana yang harus ditampilkan kepada siswa.
     */
    public function tentukanLangkahAktif()
    {
        $user = Auth::user();

        $this->langkahSelesaiIds = ProgresLangkah::where('user_id', $user->id)
            ->whereIn('langkah_id', $this->modul->langkahs->pluck('id'))
            ->pluck('langkah_id');

        // Cari langkah aktif berikutnya
        $this->langkahAktif = $this->modul->langkahs->first(fn($langkah) => !$this->langkahSelesaiIds->contains($langkah->id));

        // Atur flag 'semuaSelesai' berdasarkan apakah langkah aktif ditemukan
        $this->semuaSelesai = is_null($this->langkahAktif);

        if ($this->langkahAktif) {
            $this->langkahAktifIndex = $this->modul->langkahs->search(fn($l) => $l->id === $this->langkahAktif->id);
            $this->bisaLanjut = false; // Reset tombol lanjut
        }
    }

    /**
     * Menghitung progres dalam persen untuk progress bar.
     */
    #[Computed]
    public function progresPersen(): int
    {
        $totalLangkah = $this->modul->langkahs->count();
        if ($totalLangkah === 0) return 100; // Jika tidak ada langkah, anggap selesai

        $selesaiCount = $this->langkahSelesaiIds->count();

        return (int) (($selesaiCount / $totalLangkah) * 100);
    }

    public function aktifkanTombolLanjut()
    {
        $this->bisaLanjut = true;
    }

    /**
     * Dipanggil oleh tombol "Lanjut" dari view.
     * Menyimpan progres untuk langkah yang sedang aktif.
     */
    public function tandaiLangkahSelesai()
    {
        $user = Auth::user();

        if (in_array($user->role, ['Admin', 'Guru'])) {
            // Cari indeks dari langkah saat ini
            $currentIndex = $this->modul->langkahs->search(fn($l) => $l->id === $this->langkahAktif->id);

            // Dapatkan langkah berikutnya dari koleksi
            $nextLangkah = $this->modul->langkahs->get($currentIndex + 1);

            if ($nextLangkah) {
                // Jika ada langkah berikutnya, langsung pindah ke sana
                $this->langkahAktif = $nextLangkah;
                $this->langkahAktifIndex = $currentIndex + 1;
            } else {
                // Jika sudah di langkah terakhir, beri tahu mereka
                $this->semuaSelesai = true;
                $this->dispatch('swal', ['title' => 'Selesai', 'text' => 'Anda telah mencapai akhir dari modul ini.', 'icon' => 'info']);
            }
            return; // Hentikan eksekusi di sini untuk Admin/Guru
        }

        // Simpan progres HANYA jika pengguna adalah Siswa
        if ($user->role === 'Siswa') {
            if (!$this->langkahAktif || $this->langkahSelesaiIds->contains($this->langkahAktif->id)) {
                return;
            }

            if ($this->langkahAktif->tipe === 'soal_esai') {
                $this->validate(['jawabanEsai' => 'required|min:20']);
            }

            ProgresLangkah::create([
                'user_id' => $user->id,
                'langkah_id' => $this->langkahAktif->id,
                'status' => 'selesai',
                'waktu_selesai' => now(),
                'jawaban_teks' => $this->langkahAktif->tipe === 'soal_esai' ? $this->jawabanEsai : null
            ]);

            $this->reset('jawabanEsai');
        }

        // Pindah ke langkah berikutnya untuk SEMUA peran
        $this->tentukanLangkahAktif();

        if ($this->semuaSelesai && $user->role === 'Siswa') {
            $this->dispatch('swal', ['title' => 'Luar Biasa!', 'text' => 'Kamu telah menyelesaikan semua tantangan di pulau ini!', 'icon' => 'success']);
        }
    }

    /**
     * Memungkinkan siswa untuk kembali ke langkah yang sudah selesai.
     */
    public function goToLangkah(string $langkahId)
    {
        $targetLangkah = $this->modul->langkahs->find($langkahId);
        if (!$targetLangkah) return;

        $user = Auth::user();

        // Admin dan Guru bisa selalu bernavigasi
        if (in_array($user->role, ['Admin', 'Guru'])) {
            $this->langkahAktif = $targetLangkah;
            $this->langkahAktifIndex = $this->modul->langkahs->search(fn($l) => $l->id === $this->langkahAktif->id);
            $this->reset('jawabanEsai');
            return;
        }

        // Logika untuk Siswa (tetap sama)
        $isJelajahBebas = $this->semuaSelesai;
        if ($isJelajahBebas || $this->langkahSelesaiIds->contains($langkahId)) {
            $this->langkahAktif = $targetLangkah;
            $this->langkahAktifIndex = $this->modul->langkahs->search(fn($l) => $l->id === $this->langkahAktif->id);
            $this->reset('jawabanEsai');
        } else {
            $this->dispatch('swal', ['title' => 'Oops!', 'text' => 'Selesaikan dulu langkah saat ini ya!', 'icon' => 'warning']);
        }
    }

    public function render()
    {
        return view('livewire.pembelajaran.modul-player');
    }
}
