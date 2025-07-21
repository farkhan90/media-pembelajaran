<?php

namespace App\Livewire\Sekolah;

use App\Models\Sekolah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    // Properti untuk Modal dan Form
    public bool $sekolahModal = false;
    public bool $isEditMode = false;
    public ?Sekolah $sekolah = null; // Properti untuk menampung model Sekolah saat edit

    // Properti untuk field form
    public string $nama = '';
    public string $alamat = '';
    public string $npsn = '';
    public $logo; // <-- TAMBAHKAN INI: bisa berupa string (path) atau objek file
    public ?string $existingLogo = null;
    public bool $logoRemoved = false;

    // Properti untuk fungsionalitas tabel
    public string $search = '';
    public array $sortBy = ['column' => 'nama', 'direction' => 'asc'];
    public array $headers;

    public function mount(): void
    {
        // Mendefinisikan header tabel
        $this->headers = [
            // Tambahkan kolom untuk logo
            ['key' => 'logo', 'label' => 'Logo', 'class' => 'w-24'],
            ['key' => 'nama', 'label' => 'Nama Sekolah'],
            ['key' => 'npsn', 'label' => 'NPSN'],
        ];
    }

    // Metode untuk mengambil data sekolah dengan pencarian dan sorting
    public function sekolahs()
    {
        return Sekolah::query()
            ->when($this->search, function (Builder $query) {
                $query->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('npsn', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10); // Menggunakan paginasi
    }

    #[Computed]
    public function logoPreviewUrl(): ?string
    {
        // Prioritas 1: Jika ada file baru yang sedang di-upload, tampilkan URL sementaranya.
        if ($this->logo) {
            return $this->logo->temporaryUrl();
        }

        // Prioritas 2: Jika dalam mode edit dan ada logo lama (dan belum dihapus), tampilkan dari rute aman.
        if ($this->isEditMode && $this->existingLogo && !$this->logoRemoved) {
            return route('files.sekolah.logo', ['sekolahId' => $this->sekolah->id]);
        }

        // Jika tidak ada, kembalikan null.
        return null;
    }

    // Metode untuk menghapus logo dari UI (dipanggil oleh tombol 'Hapus Logo')
    public function removeLogo(): void
    {
        $this->logo = null;         // Hapus file baru yang mungkin sudah dipilih
        $this->logoRemoved = true;  // Tandai bahwa logo lama ingin dihapus
    }

    // Metode untuk membuka modal tambah data
    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->sekolahModal = true;
    }

    // Metode untuk membuka modal edit data
    public function edit(Sekolah $sekolah): void
    {
        $this->resetForm();
        $this->isEditMode = true;

        // Mengisi properti form dengan data yang akan diedit
        $this->sekolah = $sekolah;
        $this->nama = $sekolah->nama;
        $this->alamat = $sekolah->alamat;
        $this->npsn = $sekolah->npsn;
        $this->existingLogo = $sekolah->logo;

        $this->sekolahModal = true;
    }

    // app/Livewire/Sekolah/Index.php

    public function save(): void
    {
        // 1. Validasi input dasar (tanpa logo untuk sementara)
        $validated = $this->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'npsn' => 'required|string|max:10|unique:sekolahs,npsn' . ($this->isEditMode ? ',' . $this->sekolah->id : ''),
        ]);

        // 2. Siapkan data untuk disimpan
        $dataToSave = [
            'nama' => $validated['nama'],
            'npsn' => $validated['npsn'],
            'alamat' => $validated['alamat'],
        ];

        // 3. Logika khusus untuk menangani LOGO
        //    Ini adalah bagian yang paling penting

        // A. Jika ada logo BARU yang di-upload
        if ($this->logo) {
            // Validasi file logo secara terpisah
            $this->validate(['logo' => 'required|image|max:2048']);

            // Hapus logo lama jika ada
            if ($this->isEditMode && $this->sekolah->logo) {
                Storage::delete($this->sekolah->logo);
            }

            // Simpan logo baru dan tambahkan path-nya ke data yang akan disimpan
            $dataToSave['logo'] = $this->logo->store('sekolah-logos');
        }
        // B. Jika logo lama DIHAPUS oleh pengguna
        elseif ($this->logoRemoved && $this->isEditMode) {
            if ($this->sekolah->logo) {
                Storage::delete($this->sekolah->logo);
            }
            // Setel path logo menjadi null di data yang akan disimpan
            $dataToSave['logo'] = null;
        }
        // C. Jika TIDAK ADA PERUBAHAN pada logo (kasus "edit tanpa ganti logo")
        //    Kita tidak melakukan apa-apa. Dengan begitu, kolom 'logo' di database tidak akan tersentuh.

        // 4. Lakukan operasi database
        if ($this->isEditMode) {
            $this->sekolah->update($dataToSave);
            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Sekolah berhasil diperbarui.',
                'icon' => 'success'
            ]);
        } else {
            // Untuk data baru, kita perlu memastikan 'logo' ada di array jika di-upload
            if (isset($dataToSave['logo'])) {
                Sekolah::create($dataToSave);
            } else {
                Sekolah::create($validated); // Gunakan validated asli jika tidak ada logo
            }

            $this->dispatch('swal', [
                'title' => 'Berhasil!',
                'text' => 'Sekolah berhasil ditambahkan.',
                'icon' => 'success'
            ]);
        }

        $this->closeModal();
    }

    // Metode untuk menghapus data, dipicu oleh event konfirmasi
    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        $sekolah = Sekolah::find($id);
        if ($sekolah) {
            // Hapus file logo dari storage jika ada
            if ($sekolah->logo) {
                Storage::delete($sekolah->logo);
            }
            $sekolah->delete();
            // Kirim event SweetAlert
            $this->dispatch('swal', [
                'title' => 'Dihapus!',
                'text' => "Sekolah {$sekolah->nama} berhasil dihapus.",
                'icon' => 'success'
            ]);
        } else {
            $this->dispatch('swal', ['title' => 'Gagal!', 'text' => 'Sekolah tidak ditemukan.', 'icon' => 'error']);
        }
    }

    // Metode untuk menutup dan mereset modal
    public function closeModal(): void
    {
        $this->sekolahModal = false;
        $this->resetForm();
    }

    // Metode untuk mereset properti form
    private function resetForm(): void
    {
        $this->reset(['nama', 'alamat', 'npsn', 'logo', 'sekolah', 'isEditMode', 'existingLogo', 'logoRemoved']);
        $this->resetErrorBag();
    }

    public function render()
    {
        if (Auth::user()->role !== 'Admin') {
            abort(403, 'AKSES DITOLAK');
        }

        return view('livewire.sekolah.index', [
            'sekolahs' => $this->sekolahs(),
            'headers' => $this->headers,
        ]);
    }
}
