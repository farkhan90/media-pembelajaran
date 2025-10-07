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
use Livewire\Attributes\On;

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

    public bool $modeMengulang = false;

    public bool $sedangMengulang = false;

    public function mount(Modul $modul)
    {
        $this->modul = $modul->load('langkahs'); // Eager load langkah-langkah

        $user = Auth::user();
        if ($user->role === 'Siswa') {
            $progresLangkah = ProgresLangkah::where('user_id', $user->id)
                ->join('langkahs', 'progres_langkahs.langkah_id', '=', 'langkahs.id')
                ->join('moduls', 'langkahs.modul_id', '=', 'moduls.id')
                ->orderBy('moduls.urutan', 'desc')
                ->orderBy('langkahs.urutan', 'desc')
                ->first('moduls.urutan');

            $progresUrutan = $progresLangkah?->urutan ?? 0;
            $modulUrutan = $modul->urutan;

            // IZINKAN AKSES JIKA:
            // Urutan modul yang diakses lebih kecil atau sama dengan progres + 1
            if ($modulUrutan > $progresUrutan + 1) {
                session()->flash('pesan_error', 'Selesaikan dulu modul sebelumnya ya!');
                $this->redirect(route('peta-petualangan'), navigate: true);
                return;
            }
        }

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

        // Cek apakah semua langkah di modul INI sudah selesai
        $this->semuaSelesai = $this->modul->langkahs->count() > 0 && $this->modul->langkahs->count() === $this->langkahSelesaiIds->count();

        if ($this->modeMengulang) {
            // Jika mode mengulang, paksa mulai dari langkah pertama
            $this->langkahAktif = $this->modul->langkahs->first();
            session()->forget('mengulang_modul_id'); // Hapus session setelah digunakan
        } else {
            // Jika mode normal, cari langkah pertama yang belum selesai
            $this->langkahAktif = $this->modul->langkahs->first(fn($langkah) => !$this->langkahSelesaiIds->contains($langkah->id));
        }

        // Jika belum ada langkah aktif (karena semua sudah selesai), JANGAN set ke langkah terakhir.
        // Biarkan $langkahAktif null untuk sementara, view yang akan menanganinya.
        if ($this->langkahAktif) {
            $this->langkahAktifIndex = $this->modul->langkahs->search(fn($l) => $l->id === $this->langkahAktif->id);
            $this->bisaLanjut = false;
        } else {
            $this->langkahAktifIndex = null; // Set index ke null juga
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

    #[On('langkah-penilaian-selesai')]
    public function handlePenilaianSelesai()
    {
        // 1. Refresh state progres
        $this->tentukanLangkahAktif();

        // 2. Cek apakah ini adalah akhir dari seluruh petualangan
        if ($this->isPetualanganSelesai()) {
            // Jika ya, redirect ke halaman "Selamat!"
            return $this->redirect(route('petualangan.selesai'), navigate: true);
        }

        // 3. Jika BUKAN akhir, atau jika sedang me-review:
        //    Tampilkan SweetAlert "Langkah Selesai" dan tetap di halaman ModulPlayer
        $this->dispatch('swal', [
            'title' => 'Penilaian Selesai!',
            'text' => 'Kamu berhasil menyelesaikan tahap penilaian. Silakan lanjut ke langkah berikutnya atau kembali ke peta.',
            'icon' => 'success'
        ]);

        $this->tentukanLangkahAktif();
    }

    public function isPetualanganSelesai(): bool
    {
        $modulTerakhir = Modul::orderBy('urutan', 'desc')->first();
        if (!$modulTerakhir) return false;

        $langkahTerakhir = $modulTerakhir->langkahs()->orderBy('urutan', 'desc')->first();
        if (!$langkahTerakhir) return false;

        return ProgresLangkah::where('user_id', Auth::id())
            ->where('langkah_id', $langkahTerakhir->id)
            ->exists();
    }

    public function ulangiModul()
    {
        if (Auth::user()->role !== 'Siswa') return;

        $this->langkahAktif = $this->modul->langkahs->first();

        if ($this->langkahAktif) {
            $this->langkahAktifIndex = 0;
        }

        $this->semuaSelesai = false;
        $this->bisaLanjut = false;
        $this->reset('jawabanEsai');

        $this->dispatch('swal', [
            'title' => 'Mulai Ulang!',
            'text' => 'Petualangan di pulau ini dimulai dari awal lagi.',
            'icon' => 'info'
        ]);
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
            if (!$this->langkahAktif) return; // Pengaman jika tidak ada langkah aktif

            $isEsai = $this->langkahAktif->tipe === 'soal_esai';

            // Selalu validasi jika ini adalah langkah soal esai
            if ($isEsai) {
                $this->validate(
                    ['jawabanEsai' => 'required|string|min:20'],
                    ['jawabanEsai.min' => 'Jawabanmu masih terlalu pendek, coba ceritakan lebih banyak lagi ya!']
                );
            }

            ProgresLangkah::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'langkah_id' => $this->langkahAktif->id
                ],
                [
                    'status' => 'selesai',
                    'waktu_selesai' => now(),
                    // Simpan jawaban HANYA jika ini langkah esai
                    'jawaban_teks' => $isEsai ? $this->jawabanEsai : null
                ]
            );
            // =======================================================

            $this->reset('jawabanEsai');

            // Cek apakah seluruh petualangan selesai SETELAH me-refresh
            if ($this->isPetualanganSelesai()) {
                return $this->redirect(route('petualangan.selesai'), navigate: true);
            }

            // Setelah menyimpan, panggil SATU metode untuk me-refresh semua state
            $this->tentukanLangkahAktif();
        }

        $modulTerakhir = Modul::where('is_published', 1)->orderBy('urutan', 'desc')->first();
        if ($modulTerakhir && $this->modul->id === $modulTerakhir->id) {
            $langkahTerakhir = $modulTerakhir->langkahs()->orderBy('urutan', 'desc')->first();
            if ($langkahTerakhir && $this->langkahSelesaiIds[0] === $langkahTerakhir->id) {
                // Ini adalah akhir dari seluruh petualangan! Redirect ke halaman Selamat.
                return $this->redirect(route('petualangan.selesai'), navigate: true);
            }
        }

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

        // Otorisasi: Admin/Guru atau jika siswa sudah selesai semua, atau jika langkah target sudah selesai
        $isAllowed = in_array($user->role, ['Admin', 'Guru'])
            || $this->semuaSelesai
            || $this->langkahSelesaiIds->contains($langkahId);

        if ($isAllowed) {
            $this->langkahAktif = $targetLangkah;
            $this->langkahAktifIndex = $this->modul->langkahs->search(fn($l) => $l->id === $this->langkahAktif->id);
            $this->bisaLanjut = true; // Langsung bisa lanjut saat review

            if ($targetLangkah->tipe === 'soal_esai') {
                $progres = ProgresLangkah::where('user_id', $user->id)->where('langkah_id', $targetLangkah->id)->first();
                $this->jawabanEsai = $progres?->jawaban_teks ?? '';
            } else {
                $this->reset('jawabanEsai');
            }
        } else {
            // Jika siswa mencoba klik langkah yang masih terkunci
            $this->dispatch('swal', ['title' => 'Terkunci!', 'text' => 'Kamu harus menyelesaikan langkah sebelumnya terlebih dahulu.', 'icon' => 'warning']);
        }
    }

    public function majuKeLangkahBerikutnya($dalamProgres = false)
    {
        $currentIndex = $this->langkahAktifIndex ?? -1;
        $nextIndex = $currentIndex + 1;

        if ($nextIndex < $this->modul->langkahs->count()) {
            // Cukup panggil goToLangkah, yang sudah punya otorisasi sendiri
            $this->goToLangkah($this->modul->langkahs[$nextIndex]->id);
        } else {
            // Jika sudah di akhir, panggil tentukanLangkahAktif untuk menampilkan layar "Selesai"
            $this->tentukanLangkahAktif();
        }
    }

    public function render()
    {
        return view('livewire.pembelajaran.modul-player');
    }
}
