<?php

namespace App\Livewire\Ujian;

use App\Models\HistoriUjian;
use App\Models\Ujian;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;

#[Layout('components.layouts.ujian-layout')] // Kita akan buat layout khusus
class Pengerjaan extends Component
{
    public Ujian $ujian;
    public HistoriUjian $histori;
    public Collection $soals; // Soal yang sudah diacak
    public array $jawabanSiswa = []; // Format: [soal_id => opsi_jawaban_id]

    public int $soalIndex = 0; // Index soal yang sedang aktif
    public ?string $jawabanSoalAktif = null;

    public bool $rekapModal = false;

    public function mount(Ujian $ujian)
    {
        $this->ujian = $ujian;
        $user = auth()->user();

        // 1. Otorisasi: Pastikan siswa ada di kelas yang benar
        // (Kita akan tambahkan middleware nanti untuk ini, tapi validasi di sini juga bagus)

        if (!$user->kelas()->where('kelas_id', $ujian->kelas_id)->exists()) {
            abort(403, 'Anda tidak terdaftar di kelas untuk ujian ini.');
        }

        // SELALU BUAT HISTORI BARU SETIAP KALI UJIAN DIMULAI
        $this->histori = HistoriUjian::create([
            'ujian_id' => $this->ujian->id,
            'user_id' => $user->id,
            'waktu_mulai' => now(),
            'status' => 'Mengerjakan'
        ]);

        // Hapus sesi urutan soal lama (jika ada) untuk memastikan soal diacak kembali
        session()->forget('urutan_soal_ujian_' . $this->ujian->id);

        // Muat soal dengan urutan acak yang baru
        $this->loadSoal();

        // Tidak perlu load jawaban lama karena ini adalah pengerjaan baru
        $this->jawabanSiswa = [];
        $this->syncJawabanAktif();
    }

    // Metode BARU untuk menyinkronkan jawaban
    protected function syncJawabanAktif()
    {
        // Dapatkan ID soal yang sedang aktif
        $soalAktifId = $this->soals[$this->soalIndex]->id;

        // Cek di array $jawabanSiswa apakah sudah ada jawaban untuk soal ini
        // Jika ada, isi properti $jawabanSoalAktif. Jika tidak, buat jadi null.
        $this->jawabanSoalAktif = $this->jawabanSiswa[$soalAktifId] ?? null;
    }

    // Metode BARU yang akan dipanggil oleh wire:model saat radio button diubah
    public function updatedJawabanSoalAktif($opsiId)
    {
        // Dapatkan ID soal yang sedang aktif
        $soalAktifId = $this->soals[$this->soalIndex]->id;

        // Update array utama $jawabanSiswa
        $this->jawabanSiswa[$soalAktifId] = $opsiId;

        // Simpan ke database
        $this->histori->jawabanSiswas()->updateOrCreate(
            ['soal_id' => $soalAktifId],
            ['opsi_jawaban_id' => $opsiId]
        );
    }

    protected function loadSoal()
    {
        $urutanSoal = session('urutan_soal_' . $this->histori->id);

        if (!$urutanSoal) {
            $urutanSoal = $this->ujian->soals()->inRandomOrder()->pluck('id')->toArray();
            session(['urutan_soal_' . $this->histori->id => $urutanSoal]);
        }

        // Ambil soal berdasarkan urutan yang sudah diacak
        $this->soals = \App\Models\Soal::with('opsiJawabans')
            ->whereIn('id', $urutanSoal)
            ->orderByRaw('FIELD(id, "' . implode('","', $urutanSoal) . '")')
            ->get();
    }

    protected function loadJawaban()
    {
        // Ambil semua jawaban yang pernah disimpan untuk sesi ini
        $jawabanTersimpan = $this->histori->jawabanSiswas()->pluck('opsi_jawaban_id', 'soal_id');
        $this->jawabanSiswa = $jawabanTersimpan->toArray();
    }

    // Dipanggil setiap kali siswa memilih jawaban
    public function pilihJawaban($soalId, $opsiId)
    {
        $this->jawabanSiswa[$soalId] = $opsiId;

        // Simpan ke database secara 'upsert' (update or insert)
        $this->histori->jawabanSiswas()->updateOrCreate(
            ['soal_id' => $soalId],
            ['opsi_jawaban_id' => $opsiId]
        );
    }

    // Navigasi soal
    public function goToSoal($index)
    {
        if ($index >= 0 && $index < $this->soals->count()) {
            $this->soalIndex = $index;
            $this->syncJawabanAktif(); // <-- Panggil metode sinkronisasi baru
        }
    }

    public function nextSoal()
    {
        // Panggil goToSoal dengan index berikutnya
        $this->goToSoal($this->soalIndex + 1);
    }

    #[Computed]
    public function jumlahSoalDijawab(): int
    {
        return count($this->jawabanSiswa);
    }

    #[Computed]
    public function jumlahSoalTotal(): int
    {
        return $this->soals->count();
    }

    #[Computed]
    public function adaSoalKosong(): bool
    {
        return $this->jumlahSoalDijawab() < $this->jumlahSoalTotal();
    }

    public function openRekapModal(): void
    {
        $this->rekapModal = true;
    }

    // Menyelesaikan ujian
    public function selesaikanUjian()
    {
        // Proses perhitungan skor
        $jumlahBenar = 0;
        $jawabanSiswa = $this->histori->jawabanSiswas()->with('opsiJawaban')->get();

        foreach ($jawabanSiswa as $jawaban) {
            if ($jawaban->opsiJawaban->is_benar) {
                $jumlahBenar++;
            }
        }

        $totalSoal = $this->soals->count();
        if ($totalSoal == 0) {
            $skor = 0;
        } else {
            $skor = ($jumlahBenar / $totalSoal) * 100;
        }

        // Update histori
        $this->histori->update([
            'waktu_selesai' => now(),
            'skor_akhir' => $skor,
            'status' => 'Selesai'
        ]);

        // Hapus sesi urutan soal
        session()->forget('urutan_soal_' . $this->histori->id);

        // Redirect ke halaman hasil
        // return redirect()->route('ujian.hasil', $this->histori->id);
        //$this->dispatch('swal', ['title' => 'Selesai!', 'text' => 'Ujian telah selesai. Skor Anda: ' . round($skor, 2), 'icon' => 'success']);
        $this->dispatch('ujian-telah-selesai', [
            'title' => 'Ujian Selesai!',
            'text' => 'Skor Anda adalah: ' . round($skor, 2),
            'icon' => 'success',
            'redirectUrl' => route('ujian.list')
        ]);
    }

    public function render()
    {
        return view('livewire.ujian.pengerjaan');
    }
}
