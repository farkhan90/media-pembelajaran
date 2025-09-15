<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\ItemPertanyaan;
use App\Models\KuisMenjodohkan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class ItemManager extends Component
{
    use WithFileUploads;

    public KuisMenjodohkan $kuis;

    // Properti untuk Modal dan Form
    public bool $itemModal = false;
    public bool $isEditMode = false;
    public ?ItemPertanyaan $itemPertanyaan = null;

    // Properti untuk field form
    public string $pertanyaan_tipe = 'Teks';
    public string $pertanyaan_konten = '';
    public $pertanyaan_gambar;
    public ?string $existingGambarPertanyaan = null;

    public string $jawaban_tipe = 'Teks';
    public string $jawaban_konten = '';
    public $jawaban_gambar;
    public ?string $existingGambarJawaban = null;

    public string $kuisId;

    #[Computed]
    public function kuis()
    {
        return KuisMenjodohkan::findOrFail($this->kuisId);
    }

    public function mount(KuisMenjodohkan $kuisMenjodohkan)
    {
        $this->kuisId = $kuisMenjodohkan->id;
        // Otorisasi sederhana bisa ditambahkan di sini jika perlu
        // Contoh: if (auth()->user()->... ) { abort(403); }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->itemModal = true;
    }

    public function edit(string $itemPertanyaanId): void
    {
        $item = ItemPertanyaan::with('itemJawaban')->find($itemPertanyaanId);
        if (!$item || $item->kuis_id !== $this->kuis()->id) return;

        $this->resetForm();
        $this->isEditMode = true;
        $this->itemPertanyaan = $item;

        // Load data pertanyaan
        $this->pertanyaan_tipe = $item->tipe_item;
        if ($item->tipe_item === 'Teks') {
            $this->pertanyaan_konten = $item->konten;
        } else {
            $this->existingGambarPertanyaan = $item->konten;
        }

        // Load data jawaban
        $this->jawaban_tipe = $item->itemJawaban->tipe_item;
        if ($item->itemJawaban->tipe_item === 'Teks') {
            $this->jawaban_konten = $item->itemJawaban->konten;
        } else {
            $this->existingGambarJawaban = $item->itemJawaban->konten;
        }

        $this->itemModal = true;
    }

    #[Computed]
    public function gambarPertanyaanPreviewUrl(): ?string
    {
        if ($this->pertanyaan_gambar) {
            return $this->pertanyaan_gambar->temporaryUrl();
        }
        if ($this->isEditMode && $this->existingGambarPertanyaan) {
            return route('kuis.item-pertanyaan.gambar', ['itemPertanyaanId' => $this->itemPertanyaan->id]);
        }
        return null;
    }

    #[Computed]
    public function gambarJawabanPreviewUrl(): ?string
    {
        if ($this->jawaban_gambar) {
            return $this->jawaban_gambar->temporaryUrl();
        }
        if ($this->isEditMode && $this->existingGambarJawaban) {
            return route('kuis.item-jawaban.gambar', ['itemJawabanId' => $this->itemPertanyaan->itemJawaban->id]);
        }
        return null;
    }

    // Metode untuk menghapus preview
    public function removeGambar($tipe)
    {
        if ($tipe === 'pertanyaan') {
            $this->pertanyaan_gambar = null;
            $this->existingGambarPertanyaan = null;
        } elseif ($tipe === 'jawaban') {
            $this->jawaban_gambar = null;
            $this->existingGambarJawaban = null;
        }
    }

    public function save(): void
    {
        $this->validate([
            'pertanyaan_tipe' => 'required|in:Teks,Gambar',
            'pertanyaan_konten' => 'required_if:pertanyaan_tipe,Teks|string',
            'pertanyaan_gambar' => 'required_if:pertanyaan_tipe,Gambar|nullable|image|max:1024',
            'jawaban_tipe' => 'required|in:Teks,Gambar',
            'jawaban_konten' => 'required_if:jawaban_tipe,Teks|string',
            'jawaban_gambar' => 'required_if:jawaban_tipe,Gambar|nullable|image|max:1024',
        ]);

        DB::transaction(function () {
            // Menyiapkan data untuk ItemPertanyaan
            $dataPertanyaan = ['tipe_item' => $this->pertanyaan_tipe];
            if ($this->pertanyaan_tipe === 'Teks') {
                $dataPertanyaan['konten'] = $this->pertanyaan_konten;
            } elseif ($this->pertanyaan_gambar) {
                if ($this->isEditMode && $this->itemPertanyaan->konten && $this->itemPertanyaan->tipe_item === 'Gambar') {
                    Storage::delete($this->itemPertanyaan->konten);
                }
                $dataPertanyaan['konten'] = $this->pertanyaan_gambar->store('kuis-images');
            }

            // Menyiapkan data untuk ItemJawaban
            $dataJawaban = ['tipe_item' => $this->jawaban_tipe];
            if ($this->jawaban_tipe === 'Teks') {
                $dataJawaban['konten'] = $this->jawaban_konten;
            } elseif ($this->jawaban_gambar) {
                if ($this->isEditMode && $this->itemPertanyaan->itemJawaban->konten && $this->itemPertanyaan->itemJawaban->tipe_item === 'Gambar') {
                    Storage::delete($this->itemPertanyaan->itemJawaban->konten);
                }
                $dataJawaban['konten'] = $this->jawaban_gambar->store('kuis-images');
            }

            // Simpan atau update
            if ($this->isEditMode) {
                $this->itemPertanyaan->update($dataPertanyaan);
                $this->itemPertanyaan->itemJawaban->update($dataJawaban);
            } else {
                $newItem = $this->kuis()->itemPertanyaans()->create($dataPertanyaan);
                $newItem->itemJawaban()->create($dataJawaban);
            }
        });

        $this->closeModal();
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Pasangan item berhasil disimpan.', 'icon' => 'success']);
    }

    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        $item = ItemPertanyaan::where('id', $id)->where('kuis_id', $this->kuis()->id)->first();
        if ($item) {
            // Hapus gambar-gambar terkait jika ada
            if ($item->tipe_item === 'Gambar' && $item->konten) Storage::delete($item->konten);
            if ($item->itemJawaban->tipe_item === 'Gambar' && $item->itemJawaban->konten) Storage::delete($item->itemJawaban->konten);

            // Item jawaban akan terhapus otomatis karena cascadeOnDelete
            $item->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => "Pasangan item berhasil dihapus.", 'icon' => 'success']);
        }
    }

    public function closeModal(): void
    {
        $this->itemModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'itemModal',
            'isEditMode',
            'itemPertanyaan',
            'pertanyaan_tipe',
            'pertanyaan_konten',
            'pertanyaan_gambar',
            'existingGambarPertanyaan',
            'jawaban_tipe',
            'jawaban_konten',
            'jawaban_gambar',
            'existingGambarJawaban'
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $items = $this->kuis()->itemPertanyaans()->with('itemJawaban')->paginate(10);
        return view('livewire.kuis-menjodohkan.item-manager', ['items' => $items]);
    }
}
