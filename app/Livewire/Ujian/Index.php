<?php

namespace App\Livewire\Ujian;

use App\Models\Kelas;
use App\Models\Ujian;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Properti untuk Filter
    #[Url(as: 'kelasId', keep: true)]
    public ?string $kelasId = null;

    // Properti untuk Modal dan Form
    public bool $ujianModal = false;
    public bool $isEditMode = false;
    public bool $bantuanModal = false;
    public ?Ujian $ujian = null;

    // Properti untuk field form
    public string $judul = '';
    public string $deskripsi = '';
    public int $waktu_menit = 60;
    public string $status = 'Draft';

    // Properti Tabel
    public string $search = '';
    public array $headers;

    public function mount(): void
    {
        $this->headers = [
            ['key' => 'judul', 'label' => 'Judul Kuis 1'],
            ['key' => 'deskripsi', 'label' => 'Deskripsi'],
            ['key' => 'waktu_menit', 'label' => 'Waktu (Menit)', 'class' => 'w-32 text-center'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-32 text-center'],
            ['key' => 'soals_count', 'label' => 'Jumlah Soal', 'class' => 'w-32 text-center'],
        ];
    }

    // Opsi Kelas berdasarkan Role (kita gunakan lagi logika ini)
    #[Computed]
    public function kelasOptions()
    {
        $user = Auth::user();
        $query = Kelas::query();

        if ($user->role === 'Guru') {
            // Guru hanya bisa memilih kelas yang diampunya
            $query->where('guru_pengampu_id', $user->id);
        }

        return $query->with('sekolah')->orderBy('nama')->get()->map(function ($kelas) {
            return [
                'id' => $kelas->id,
                'name' => "{$kelas->sekolah->nama} - {$kelas->nama}"
            ];
        });
    }

    // Daftar ujian berdasarkan filter kelas
    #[Computed]
    public function ujians()
    {
        if (!$this->kelasId) {
            return collect();
        }

        return Ujian::query()
            ->withCount('soals') // Menghitung jumlah soal terkait
            ->where('kelas_id', $this->kelasId)
            ->when($this->search, fn($q) => $q->where('judul', 'like', "%{$this->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[On('ujian-updated')]
    public function refreshUjianList()
    {
        // Cukup render ulang untuk mendapatkan data terbaru
    }

    // Dipanggil saat filter kelas diubah
    public function updatedKelasId()
    {
        $this->resetPage();
    }

    // Membuka modal tambah data
    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->ujianModal = true;
    }

    // Membuka modal edit data
    public function edit(string $ujianId): void
    {
        $ujian = Ujian::find($ujianId);
        if (!$ujian) return;

        $this->resetForm();
        $this->isEditMode = true;

        $this->ujian = $ujian;
        $this->judul = $ujian->judul;
        $this->deskripsi = $ujian->deskripsi;
        $this->waktu_menit = $ujian->waktu_menit;
        $this->status = $ujian->status;

        $this->ujianModal = true;
    }

    // Menyimpan data ujian
    public function save(): void
    {
        $validated = $this->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_menit' => 'required|integer|min:1',
            'status' => 'required|in:Draft,Published',
        ]);

        $dataToSave = [
            'kelas_id' => $this->kelasId,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'waktu_menit' => $validated['waktu_menit'],
            'status' => $validated['status'],
        ];

        if ($this->isEditMode) {
            if ($this->ujian->judul !== $dataToSave['judul']) {
                $dataToSave['slug'] = Str::slug($dataToSave['judul']) . '-' . strtolower(Str::random(5));
            }

            $this->ujian->update($dataToSave);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kuis 1 berhasil diperbarui.', 'icon' => 'success']);
        } else {
            $dataToSave['slug'] = Str::slug($dataToSave['judul']) . '-' . strtolower(Str::random(5));

            Ujian::create($dataToSave);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kuis 1 berhasil dibuat.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    // Menghapus ujian
    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        $ujian = Ujian::find($id);
        if ($ujian) {
            // Karena kita menggunakan cascadeOnDelete, semua soal, opsi, histori, dll
            // yang terkait dengan ujian ini akan otomatis terhapus.
            $ujian->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => "Ujian berhasil dihapus.", 'icon' => 'success']);
        }
    }

    // Menutup modal
    public function closeModal(): void
    {
        $this->ujianModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['judul', 'deskripsi', 'waktu_menit', 'status', 'ujian', 'isEditMode']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.ujian.index');
    }
}
