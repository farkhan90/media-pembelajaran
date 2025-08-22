<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\Kelas;
use App\Models\KuisMenjodohkan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class DuplikasiKuis extends Component
{
    public string $kelasTujuanId; // ID Kelas saat ini (tujuan duplikasi)
    public bool $duplikasiModal = false;

    // Properti untuk filter
    public ?string $kelasSumberId = null;
    public ?string $kuisSumberId = null;

    #[On('open-duplikasi-kuis-modal')]
    public function openModal()
    {
        $this->resetFilters();
        $this->duplikasiModal = true;
    }

    private function resetFilters()
    {
        $this->reset(['kelasSumberId', 'kuisSumberId']);
    }

    // Opsi Kelas Sumber (logika otorisasi yang sama)
    #[Computed(cache: true)]
    public function kelasSumberOptions()
    {
        $user = Auth::user();
        $query = Kelas::query();

        if ($user->role === 'Guru') {
            $query->where('guru_pengampu_id', $user->id);
        }

        return $query->with('sekolah')->orderBy('nama')->get()->map(function ($kelas) {
            return ['id' => $kelas->id, 'name' => "{$kelas->sekolah->nama} - {$kelas->nama}"];
        });
    }

    // Opsi Kuis Sumber
    #[Computed]
    public function kuisSumberOptions()
    {
        if (!$this->kelasSumberId) {
            return collect();
        }
        return KuisMenjodohkan::where('kelas_id', $this->kelasSumberId)->where('status', 'Published')->get(['id', 'judul']);
    }

    public function updatedKelasSumberId()
    {
        $this->reset('kuisSumberId');
    }

    // Metode utama untuk memproses duplikasi
    public function prosesDuplikasi()
    {
        $this->validate([
            'kuisSumberId' => 'required|exists:kuis_menjodohkan,id'
        ]);

        $kuisSumber = KuisMenjodohkan::with('itemPertanyaans.itemJawaban')->find($this->kuisSumberId);

        if (!$kuisSumber) {
            $this->dispatch('swal', ['title' => 'Gagal!', 'text' => 'Kuis sumber tidak ditemukan.', 'icon' => 'error']);
            return;
        }

        DB::transaction(function () use ($kuisSumber) {
            // 1. Duplikasi Kuis utama
            $kuisBaru = KuisMenjodohkan::create([
                'kelas_id' => $this->kelasTujuanId,
                'judul' => $kuisSumber->judul . ' (Salinan)',
                'deskripsi' => $kuisSumber->deskripsi,
                'status' => 'Draft',
            ]);

            // 2. Loop melalui setiap item pertanyaan di kuis sumber
            foreach ($kuisSumber->itemPertanyaans as $itemPertanyaanAsli) {
                // 3. Duplikasi Item Pertanyaan
                $itemPertanyaanBaru = $kuisBaru->itemPertanyaans()->create([
                    'tipe_item' => $itemPertanyaanAsli->tipe_item,
                    'konten' => $itemPertanyaanAsli->konten,
                    // Duplikasi gambar bisa ditambahkan di sini jika perlu
                ]);

                // 4. Duplikasi Item Jawaban yang berpasangan
                if ($itemJawabanAsli = $itemPertanyaanAsli->itemJawaban) {
                    $itemPertanyaanBaru->itemJawaban()->create([
                        'tipe_item' => $itemJawabanAsli->tipe_item,
                        'konten' => $itemJawabanAsli->konten,
                    ]);
                }
            }
        });

        $this->duplikasiModal = false;
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kuis berhasil diduplikasi.', 'icon' => 'success']);
        // Beri tahu komponen induk (Kuis/Index) untuk me-refresh daftarnya
        $this->dispatch('kuis-updated');
    }

    public function render()
    {
        return view('livewire.kuis-menjodohkan.duplikasi-kuis');
    }
}
