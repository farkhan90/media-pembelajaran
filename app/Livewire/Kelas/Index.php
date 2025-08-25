<?php

namespace App\Livewire\Kelas;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
    public bool $kelasModal = false;
    public bool $isEditMode = false;
    public ?Kelas $kelas = null;

    // Properti untuk field form
    public string $nama = '';
    public string $sekolah_id = ''; // Untuk menampung ID sekolah yang dipilih
    public ?string $guru_pengampu_id = null;
    public string $guruSearch = '';

    public bool $bantuanModal = false;

    // Properti untuk fungsionalitas tabel
    public string $search = '';
    public array $sortBy = ['column' => 'nama', 'direction' => 'asc'];
    public array $headers;

    // Computed property untuk mengambil opsi sekolah
    #[Computed]
    public function sekolahOptions(): array
    {
        return Sekolah::query()
            ->orderBy('nama')
            ->get(['id', 'nama']) // Ambil kolom 'id' dan 'nama'
            ->map(function ($sekolah) {
                // Ubah format setiap item dalam collection
                return ['id' => $sekolah->id, 'name' => $sekolah->nama];
            })
            ->toArray(); // Sekarang ubah menjadi array
    }

    public function mount(): void
    {
        $this->headers = [
            ['key' => 'nama', 'label' => 'Nama Kelas'],
            ['key' => 'sekolah.nama', 'label' => 'Nama Sekolah'],
            ['key' => 'guruPengampu.nama', 'label' => 'Guru Kelas'],
        ];
    }

    // Metode untuk mengambil data kelas dengan relasi, pencarian, dan sorting
    public function kelases()
    {
        return Kelas::query()
            ->with(['sekolah', 'guruPengampu'])
            ->when($this->search, function (Builder $query) {
                $query->where('nama', 'like', "%{$this->search}%")
                    // Pencarian pada nama sekolah melalui relasi
                    ->orWhereHas('sekolah', function (Builder $q) {
                        $q->where('nama', 'like', "%{$this->search}%");
                    });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    // Metode untuk membuka modal tambah data
    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->kelasModal = true;
    }

    // Metode untuk membuka modal edit data
    public function edit(string $kelasId): void
    {
        $kelas = Kelas::find($kelasId);
        if (!$kelas) return;

        $this->resetForm();
        $this->isEditMode = true;

        $this->kelas = $kelas;
        $this->nama = $kelas->nama;
        $this->sekolah_id = $kelas->sekolah_id;
        $this->guru_pengampu_id = $kelas->guru_pengampu_id;

        $this->kelasModal = true;
    }

    // Metode untuk menyimpan data
    public function save(): void
    {
        if (empty($this->guru_pengampu_id)) {
            $this->guru_pengampu_id = null;
        }

        // Validasi yang lebih canggih untuk memastikan nama kelas unik per sekolah
        $uniqueRule = Rule::unique('kelas')->where('sekolah_id', $this->sekolah_id);
        if ($this->isEditMode) {
            $uniqueRule->ignore($this->kelas->id);
        }

        $validated = $this->validate([
            'nama' => ['required', 'string', 'max:255', $uniqueRule],
            'sekolah_id' => 'required|exists:sekolahs,id',
            'guru_pengampu_id' => 'nullable|exists:users,id,role,Guru',
        ]);

        if ($this->isEditMode) {
            $this->kelas->update($validated);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kelas berhasil diperbarui.', 'icon' => 'success']);
        } else {
            Kelas::create($validated);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Kelas berhasil ditambahkan.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    // Metode untuk menghapus data
    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        $kelas = Kelas::find($id);
        if ($kelas) {
            $kelas->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => "Kelas {$kelas->nama} berhasil dihapus.", 'icon' => 'success']);
        }
    }

    // Metode untuk menutup dan mereset modal
    public function closeModal(): void
    {
        $this->kelasModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['nama', 'sekolah_id', 'kelas', 'isEditMode', 'guru_pengampu_id', 'guruSearch']);
        $this->resetErrorBag();
    }

    #[Computed]
    public function guruOptions(): array
    {
        return User::where('role', 'Guru')
            ->when($this->guruSearch, function ($query) {
                // Cari berdasarkan nama atau email
                $query->where('nama', 'like', "%{$this->guruSearch}%")
                    ->orWhere('email', 'like', "%{$this->guruSearch}%");
            })
            ->orderBy('nama')
            ->get(['id', 'nama'])
            ->map(fn($guru) => ['id' => $guru->id, 'nama' => $guru->nama])
            ->toArray();
    }

    public function render()
    {
        // Lapisan pertahanan kedua untuk memastikan hanya Admin yang bisa mengakses
        if (Auth::user()->role !== 'Admin') {
            abort(403, 'AKSES DITOLAK');
        }

        return view('livewire.kelas.index', [
            'kelases' => $this->kelases(),
            'headers' => $this->headers,
        ]);
    }
}
