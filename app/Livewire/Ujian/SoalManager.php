<?php

namespace App\Livewire\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SoalManager extends Component
{
    use WithPagination;

    public Ujian $ujian;

    // Listener untuk me-refresh data setelah soal disimpan
    #[On('soal-saved')]
    public function refreshSoals()
    {
        // Cukup render ulang komponen untuk mendapatkan data terbaru
    }

    public function mount(Ujian $ujian)
    {
        $this->ujian = $ujian;
    }

    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        $soal = Soal::where('id', $id)->where('ujian_id', $this->ujian->id)->first();
        if ($soal) {
            if ($soal->gambar_soal) {
                Storage::delete($soal->gambar_soal);
            }
            $soal->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => "Soal berhasil dihapus.", 'icon' => 'success']);
        }
    }

    public function render()
    {
        $soals = $this->ujian->soals()->with('opsiJawabans')->paginate(10);
        return view('livewire.ujian.soal-manager', ['soals' => $soals]);
    }
}
