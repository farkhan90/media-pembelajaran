<?php

namespace App\Livewire\Siswa;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // <-- Tambahkan ini
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Manage extends Component
{
    use WithPagination, WithFileUploads;

    // Properti untuk Filter
    public ?string $sekolahId = null;
    public ?string $kelasId = null;

    // Properti untuk Modal Transfer Siswa
    public bool $transferModal = false;
    public bool $bantuanModal = false;
    public array $siswaTanpaKelasIds = [];
    public array $siswaDiKelasIds = [];

    // Properti Tabel
    public string $search = '';
    public array $headers;

    public bool $createSiswaModal = false;
    // Properti untuk form siswa baru
    public string $nama = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $jk = 'L';
    public $foto;

    public function mount()
    {
        $this->headers = [
            ['key' => 'foto', 'label' => 'Foto', 'class' => 'w-16'],
            ['key' => 'nama', 'label' => 'Nama Siswa'],
            ['key' => 'email', 'label' => 'Email'],
        ];
    }

    // Opsi Sekolah berdasarkan Role
    #[Computed]
    public function sekolahOptions()
    {
        if (Auth::user()->role === 'Admin') {
            return Sekolah::orderBy('nama')->get();
        }

        // Guru hanya melihat sekolah tempat kelas yang diampunya berada
        return Sekolah::whereHas('kelas', function ($query) {
            $query->whereHas('guruPengampu', function ($q) {
                $q->where('guru_pengampu_id', Auth::id());
            });
        })->orderBy('nama')->get();
    }

    // Opsi Kelas berdasarkan Sekolah yang dipilih dan Role
    #[Computed]
    public function kelasOptions()
    {
        if (!$this->sekolahId) {
            return collect();
        }

        $query = Kelas::where('sekolah_id', $this->sekolahId);

        if (Auth::user()->role === 'Guru') {
            // Guru hanya bisa memilih kelas yang diampunya di sekolah tersebut
            $query->whereHas('guruPengampu', function ($q) {
                $q->where('guru_pengampu_id', Auth::id());
            });
        }

        return $query->orderBy('nama')->get();
    }

    // Daftar siswa berdasarkan filter kelas
    #[Computed]
    public function siswas()
    {
        // Jangan tampilkan apa-apa jika kelas belum dipilih
        if (!$this->kelasId) {
            return collect();
        }

        return User::query()
            ->where('role', 'Siswa')
            ->whereHas('kelas', fn($q) => $q->where('kelas.id', $this->kelasId))
            ->when($this->search, fn($q) => $q->where('nama', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('nama')
            ->paginate(15, pageName: 'siswa-di-kelas');
    }

    // Daftar siswa yang belum punya kelas sama sekali
    #[Computed]
    public function siswaBelumPunyaKelas()
    {
        return User::where('role', 'Siswa')
            ->whereDoesntHave('kelas')
            ->select('id', 'nama', 'email', 'foto') // <-- Tambahkan 'foto'
            ->orderBy('nama')
            ->get();
    }

    // Dipanggil saat filter sekolah diubah
    public function updatedSekolahId()
    {
        // Reset filter kelas dan paginasi
        $this->reset(['kelasId', 'search']);
        $this->resetPage('siswa-di-kelas');
    }

    // Dipanggil saat filter kelas diubah
    public function updatedKelasId()
    {
        // Reset pencarian dan paginasi
        $this->reset('search');
        $this->resetPage('siswa-di-kelas');
    }

    // Membuka modal transfer
    public function openTransferModal()
    {
        $this->transferModal = true;
    }

    // Logika untuk memindahkan siswa
    public function transferSiswa()
    {
        if (!$this->kelasId) {
            $this->dispatch('swal', ['title' => 'Gagal', 'text' => 'Pilih kelas tujuan terlebih dahulu.', 'icon' => 'error']);
            return;
        }

        $this->validate([
            'siswaTanpaKelasIds' => 'array',
            'siswaTanpaKelasIds.*' => 'exists:users,id',
            'siswaDiKelasIds' => 'array',
            'siswaDiKelasIds.*' => 'exists:users,id',
        ]);

        DB::transaction(function () {
            $kelas = Kelas::find($this->kelasId);

            // Masukkan siswa baru ke kelas
            if (!empty($this->siswaTanpaKelasIds)) {
                $kelas->siswa()->attach($this->siswaTanpaKelasIds);
            }

            // Keluarkan siswa dari kelas
            if (!empty($this->siswaDiKelasIds)) {
                $kelas->siswa()->detach($this->siswaDiKelasIds);
            }
        });

        $this->reset(['siswaTanpaKelasIds', 'siswaDiKelasIds']);
        $this->transferModal = false;
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Perubahan data siswa berhasil disimpan.', 'icon' => 'success']);
    }

    // Membuka modal
    public function openCreateSiswaModal(): void
    {
        $this->resetFormSiswa();
        $this->createSiswaModal = true;
    }

    // Menyimpan siswa baru
    public function saveSiswa(): void
    {
        // Pastikan kelas tujuan sudah dipilih
        if (!$this->kelasId) {
            $this->dispatch('swal', ['title' => 'Gagal', 'text' => 'Tidak ada kelas tujuan yang dipilih.', 'icon' => 'error']);
            return;
        }

        $validated = $this->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'jk' => 'required|in:L,P',
            'foto' => 'nullable|image|max:1024',
        ]);

        DB::transaction(function () use ($validated) {
            $dataToSave = [
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'jk' => $validated['jk'],
                'role' => 'Siswa', // Role sudah pasti 'Siswa'
            ];

            // Handle upload foto jika ada
            if ($this->foto) {
                $dataToSave['foto'] = $this->foto->store('user-fotos');
            }

            // Buat user baru
            $newSiswa = User::create($dataToSave);

            // Langsung masukkan siswa ke kelas yang sedang aktif/difilter
            $kelas = Kelas::find($this->kelasId);
            $kelas->siswa()->attach($newSiswa->id);
        });

        $this->createSiswaModal = false;
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => "Siswa {$this->nama} berhasil dibuat dan ditambahkan ke kelas.", 'icon' => 'success']);
    }

    // Mereset field form siswa
    private function resetFormSiswa(): void
    {
        $this->reset(['nama', 'email', 'password', 'password_confirmation', 'jk', 'foto']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.siswa.manage');
    }
}
