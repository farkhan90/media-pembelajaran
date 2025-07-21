<?php

namespace App\Livewire\Ujian;

use App\Models\Kelas;
use App\Models\Ujian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DuplikasiUjian extends Component
{
    public string $kelasTujuanId; // ID Kelas saat ini (tujuan duplikasi)

    public bool $duplikasiModal = false;

    // Properti untuk filter
    public ?string $kelasSumberId = null;
    public ?string $ujianSumberId = null;

    // Listener untuk membuka modal
    #[On('open-duplikasi-modal')]
    public function openModal()
    {
        $this->resetFilters();
        $this->duplikasiModal = true;
    }

    private function resetFilters()
    {
        $this->reset(['kelasSumberId', 'ujianSumberId']);
    }

    // Opsi Kelas Sumber dengan otorisasi
    #[Computed]
    public function kelasSumberOptions()
    {
        $user = Auth::user();
        $query = Kelas::query();

        if ($user->role === 'Guru') {
            // Guru hanya bisa duplikasi dari kelas yang diampunya
            $query->where('guru_pengampu_id', $user->id);
        }

        return $query->with('sekolah')->orderBy('nama')->get()->map(function ($kelas) {
            return ['id' => $kelas->id, 'name' => "{$kelas->sekolah->nama} - {$kelas->nama}"];
        });
    }

    // Opsi Ujian Sumber, bergantung pada kelas yang dipilih
    #[Computed]
    public function ujianSumberOptions()
    {
        if (!$this->kelasSumberId) {
            return collect();
        }
        return Ujian::where('kelas_id', $this->kelasSumberId)->where('status', 'Published')->get(['id', 'judul']);
    }

    // Dipanggil saat kelas sumber diubah
    public function updatedKelasSumberId()
    {
        $this->reset('ujianSumberId');
    }

    // Metode utama untuk memproses duplikasi
    public function prosesDuplikasi()
    {
        $this->validate([
            'ujianSumberId' => 'required|exists:ujians,id'
        ]);

        $ujianSumber = Ujian::with('soals.opsiJawabans')->find($this->ujianSumberId);

        if (!$ujianSumber) {
            $this->dispatch('swal', ['title' => 'Gagal!', 'text' => 'Ujian sumber tidak ditemukan.', 'icon' => 'error']);
            return;
        }

        DB::transaction(function () use ($ujianSumber) {
            // 1. Duplikasi Ujian utama
            $ujianBaru = Ujian::create([
                'kelas_id' => $this->kelasTujuanId,
                'judul' => $ujianSumber->judul . ' (Salinan)', // Tambahkan penanda
                'deskripsi' => $ujianSumber->deskripsi,
                'waktu_menit' => $ujianSumber->waktu_menit,
                'status' => 'Draft', // Setel ke Draft agar bisa diedit dulu
            ]);

            // 2. Loop melalui setiap soal di ujian sumber
            foreach ($ujianSumber->soals as $soalAsli) {
                // 3. Duplikasi Soal
                $soalBaru = $ujianBaru->soals()->create([
                    'pertanyaan' => $soalAsli->pertanyaan,
                    // Logika duplikasi gambar bisa ditambahkan di sini jika perlu
                ]);

                // 4. Siapkan data opsi untuk diduplikasi
                $opsiBaruData = $soalAsli->opsiJawabans->map(function ($opsiAsli) {
                    return [
                        'teks_opsi' => $opsiAsli->teks_opsi,
                        'is_benar' => $opsiAsli->is_benar,
                    ];
                })->toArray();

                // 5. Buat ulang opsi di bawah soal yang baru
                if (!empty($opsiBaruData)) {
                    $soalBaru->opsiJawabans()->createMany($opsiBaruData);
                }
            }
        });

        $this->duplikasiModal = false;
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Ujian berhasil diduplikasi.', 'icon' => 'success']);
        // Beri tahu komponen induk (Ujian/Index) untuk me-refresh daftarnya
        $this->dispatch('ujian-updated');
    }

    public function render()
    {
        return view('livewire.ujian.duplikasi-ujian');
    }
}
