<?php

namespace App\Livewire\Laporan;

use App\Models\Langkah;
use App\Models\Modul;
use App\Models\ProgresLangkah;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class JawabanEsai extends Component
{
    use WithPagination;

    public ?string $modulId = null;
    public ?string $langkahId = null;
    public string $search = '';

    #[Computed(cache: true)]
    public function modulOptions()
    {
        return Modul::orderBy('urutan')->get();
    }

    #[Computed]
    public function langkahOptions()
    {
        if (!$this->modulId) return collect();
        return Langkah::where('modul_id', $this->modulId)
            ->where('tipe', 'soal_esai')
            ->orderBy('urutan')
            ->get();
    }

    #[Computed]
    public function jawabans()
    {
        if (!$this->langkahId) {
            return ProgresLangkah::where('id', false)->get();
        }

        $query = ProgresLangkah::query()
            ->where('langkah_id', $this->langkahId)
            ->whereNotNull('jawaban_teks')
            ->with(['user.kelas']);

        // Otorisasi guru
        if (Auth::user()->role === 'Guru') {
            $kelasDiampuIds = Auth::user()->kelasDiampu->pluck('id');
            $query->whereHas('user.kelas', fn($q) => $q->whereIn('kelas.id', $kelasDiampuIds));
        }

        if ($this->search) {
            $query->whereHas('user', fn($q) => $q->where('nama', 'like', "%{$this->search}%"));
        }

        return $query->orderBy('waktu_selesai', 'desc')->paginate(10);
    }

    public function updatedModulId()
    {
        // Saat modul berubah, reset pilihan langkah DAN paginasi.
        $this->reset('langkahId');
        $this->resetPage();
    }

    public function updatedLangkahId()
    {
        // Saat langkah berubah, HANYA reset paginasi.
        $this->resetPage();
    }

    // Tambahkan ini juga untuk pencarian
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.laporan.jawaban-esai');
    }
}
