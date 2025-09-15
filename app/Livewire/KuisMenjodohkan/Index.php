<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\KuisMenjodohkan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Properti untuk Modal dan Form
    public bool $kuisModal = false;
    public bool $isEditMode = false;
    public bool $bantuanModal = false;
    public ?KuisMenjodohkan $kuis = null;

    // Properti untuk field form
    public string $judul = '';
    public string $deskripsi = '';
    public string $status = 'Draft';

    // Properti Tabel
    public string $search = '';
    public array $headers;

    public function mount(): void
    {
        $this->headers = [
            ['key' => 'judul', 'label' => 'Judul Kuis'],
            ['key' => 'deskripsi', 'label' => 'Deskripsi'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32 text-center'],
            ['key' => 'item_pertanyaans_count', 'label' => 'Jumlah Pasangan', 'class' => 'w-32 text-center'],
        ];
    }

    // Daftar kuis berdasarkan filter kelas
    #[Computed]
    public function kuises()
    {
        return KuisMenjodohkan::query()
            ->withCount('itemPertanyaans')
            ->when($this->search, fn($q) => $q->where('judul', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[On('kuis-updated')]
    public function refreshKuisList()
    {
        // Cukup render ulang untuk mendapatkan data terbaru
        // Dengan adanya #[Computed], Livewire akan otomatis me-refresh `kuises()`
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->kuisModal = true;
    }

    public function edit(string $kuisId): void
    {
        $kuis = KuisMenjodohkan::find($kuisId);
        if (!$kuis) return;

        $this->resetForm();
        $this->isEditMode = true;

        $this->kuis = $kuis;
        $this->judul = $kuis->judul;
        $this->deskripsi = $kuis->deskripsi;
        $this->status = $kuis->status;

        $this->kuisModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:Draft,Published',
        ]);

        if ($this->isEditMode) {
            $this->kuis->update($validated);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kuis berhasil diperbarui.', 'icon' => 'success']);
        } else {
            KuisMenjodohkan::create($validated);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kuis berhasil dibuat.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        $kuis = KuisMenjodohkan::find($id);
        if ($kuis) {
            $kuis->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => "Kuis berhasil dihapus.", 'icon' => 'success']);
        }
    }

    public function closeModal(): void
    {
        $this->kuisModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['judul', 'deskripsi', 'status', 'kuis', 'isEditMode']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.kuis-menjodohkan.index');
    }
}
