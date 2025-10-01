<?php

namespace App\Livewire\Modul;

use App\Models\Modul;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    public bool $modulModal = false;
    public bool $isEditMode = false;
    public ?Modul $modul = null;

    // Properti Form
    public string $judul = '';
    public string $nama_pulau = '';
    public $gambar_pulau;
    public ?string $existingGambar = null;
    public int $urutan = 1;
    public bool $is_published = false;

    public bool $bantuanModal = false;

    // Properti Tabel
    public array $headers;

    public function mount(): void
    {
        $this->headers = [
            ['key' => 'urutan', 'label' => 'No.', 'class' => 'w-1'],
            ['key' => 'gambar_pulau', 'label' => 'Gambar', 'class' => 'w-24'],
            ['key' => 'judul', 'label' => 'Judul Modul (Pulau)'],
            ['key' => 'langkahs_count', 'label' => 'Jml. Langkah', 'class' => 'w-32 text-center'],
            ['key' => 'is_published', 'label' => 'Status', 'class' => 'w-32 text-center'],
        ];
    }

    /**
     * Membuka modal dalam mode 'create'.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        // Secara otomatis mengisi nomor urutan berikutnya
        $this->urutan = Modul::max('urutan') + 1;
        $this->modulModal = true;
    }

    /**
     * Membuka modal dalam mode 'edit' dan mengisi form dengan data yang ada.
     */
    public function edit(Modul $modul): void
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->modul = $modul;

        $this->judul = $modul->judul;
        $this->nama_pulau = $modul->nama_pulau;
        $this->urutan = $modul->urutan;
        $this->is_published = $modul->is_published;
        $this->existingGambar = $modul->gambar_pulau;

        $this->modulModal = true;
    }

    /**
     * Menyimpan data (baik baru maupun editan) ke database.
     */
    public function save(): void
    {
        // 1. Validasi data utama (tanpa gambar untuk saat ini)
        $validated = $this->validate([
            'judul' => 'required|string|max:255',
            'nama_pulau' => 'required|string|alpha_dash|max:255|unique:moduls,nama_pulau' . ($this->isEditMode ? ',' . $this->modul->id : ''),
            'urutan' => 'required|integer|min:1',
            'is_published' => 'required|boolean',
        ]);

        // 2. Siapkan data dasar untuk disimpan
        $dataToSave = $validated;

        // 3. Logika khusus untuk menangani GAMBAR
        // Validasi gambar secara terpisah
        $this->validate([
            'gambar_pulau' => ($this->isEditMode && !$this->gambar_pulau) ? 'nullable' : 'required' . '|mimes:jpeg,png,jpg,gif,svg'
        ]);

        // A. Jika ada gambar BARU yang diunggah
        if ($this->gambar_pulau) {
            // Hapus gambar lama jika sedang mengedit & ada gambar lama
            if ($this->isEditMode && $this->modul->gambar_pulau) {
                Storage::delete($this->modul->gambar_pulau);
            }
            // Simpan gambar baru dan tambahkan path-nya ke data yang akan disimpan
            $dataToSave['gambar_pulau'] = $this->gambar_pulau->store('pulau-images');
        }
        // B. JIKA TIDAK ADA GAMBAR BARU, kita tidak melakukan apa-apa.
        //    Dengan begitu, kunci 'gambar_pulau' tidak akan ada di $dataToSave,
        //    dan Eloquent tidak akan menyentuh kolom tersebut saat update.

        // 4. Lakukan operasi database
        if ($this->isEditMode) {
            $this->modul->update($dataToSave);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Modul berhasil diperbarui.', 'icon' => 'success']);
        } else {
            // Saat membuat baru, kita harus yakin path gambar ada di data
            // (sudah ditangani oleh validasi di atas)
            Modul::create($dataToSave);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Modul berhasil ditambahkan.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    /**
     * Menghapus data modul.
     */
    #[On('delete-confirmed')]
    public function delete(string $modulId): void
    {
        $modul = Modul::find($modulId);
        if ($modul) {
            // Hapus gambar terkait dari storage
            if ($modul->gambar_pulau) {
                Storage::delete($modul->gambar_pulau);
            }
            // Karena cascadeOnDelete di migrasi, semua langkah terkait akan terhapus
            $modul->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => 'Modul berhasil dihapus.', 'icon' => 'success']);
        }
    }

    public function closeModal(): void
    {
        $this->modulModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'modulModal',
            'isEditMode',
            'modul',
            'judul',
            'nama_pulau',
            'gambar_pulau',
            'existingGambar',
            'urutan',
            'is_published'
        ]);

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $moduls = Modul::withCount('langkahs') // Eager load hitungan langkah
            ->orderBy('urutan')
            ->paginate(10);

        return view('livewire.modul.index', ['moduls' => $moduls]);
    }
}
