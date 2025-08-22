<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Exports\UsersTemplateExport;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination, WithFileUploads;

    // Properti untuk Modal dan Form
    public bool $userModal = false;
    public bool $isEditMode = false;
    public ?User $user = null;

    // Properti untuk field form
    public string $nama = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'Siswa';
    public string $jk = 'L';
    public $foto;
    public ?string $existingFoto = null;
    public bool $fotoRemoved = false;

    // Properti untuk fungsionalitas tabel
    public string $search = '';
    public array $sortBy = ['column' => 'nama', 'direction' => 'asc'];
    public array $headers;

    public bool $imporModal = false;
    public $fileImpor;

    // Metode untuk download template
    public function downloadTemplate()
    {
        return Excel::download(new UsersTemplateExport, 'template_user.xlsx');
    }

    // Metode untuk memproses impor
    public function impor()
    {
        $this->validate([
            'fileImpor' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new UsersImport, $this->fileImpor);

            $this->imporModal = false;
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Data user berhasil diimpor.', 'icon' => 'success']);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            // Kirim pesan error yang lebih detail (opsional)
            $this->dispatch('swal', ['title' => 'Impor Gagal', 'text' => 'Ada kesalahan pada data di baris ' . $failures[0]->row() . ': ' . $failures[0]->errors()[0], 'icon' => 'error']);
        }
    }

    public function mount(): void
    {
        $this->headers = [
            ['key' => 'foto', 'label' => 'Foto', 'class' => 'w-16'],
            ['key' => 'nama', 'label' => 'Nama'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'role', 'label' => 'Role', 'class' => 'w-32'],
        ];
    }

    // Metode untuk mengambil data user
    public function users()
    {
        return User::query()
            ->when($this->search, function (Builder $query) {
                $query->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    #[Computed]
    public function fotoPreviewUrl(): ?string
    {
        // Prioritas 1: Tampilkan file baru yang sedang di-upload
        if ($this->foto) {
            try {
                return $this->foto->temporaryUrl();
            } catch (\Exception $e) {
                // Tangani error jika URL sementara tidak bisa dibuat
                return null;
            }
        }

        // Prioritas 2: Tampilkan foto yang sudah ada saat mode edit
        if ($this->isEditMode && $this->existingFoto && !$this->fotoRemoved) {
            return route('files.user.foto', ['userId' => $this->user->id]);
        }

        // Default: tidak ada preview
        return null;
    }

    public function removeFoto(): void
    {
        $this->foto = null;
        $this->fotoRemoved = true;
    }

    // Metode untuk membuka modal tambah data
    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->userModal = true;
    }

    // Metode untuk membuka modal edit data
    public function edit(string $userId): void
    {
        $user = User::find($userId);
        if (!$user) return;

        $this->resetForm();
        $this->isEditMode = true;

        $this->user = $user;
        $this->nama = $user->nama;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->jk = $user->jk;
        $this->existingFoto = $user->foto;

        // Penting: jangan pernah memuat password ke form
        $this->password = '';
        $this->password_confirmation = '';

        $this->userModal = true;
    }

    // Metode untuk menyimpan data
    public function save(): void
    {
        // Aturan validasi dinamis
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->isEditMode ? ',' . $this->user->id : ''),
            'role' => ['required', Rule::in(['Admin', 'Guru', 'Siswa'])],
            'jk' => ['required', Rule::in(['L', 'P'])],
            'foto' => 'nullable|image|max:1024',
        ];

        // Tambahkan validasi password hanya jika diisi (untuk edit) atau saat membuat baru
        if (!$this->isEditMode || !empty($this->password)) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $validated = $this->validate($rules);

        $dataToSave = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'jk' => $validated['jk'],
        ];

        // Tambahkan password ke data simpan HANYA jika diisi
        if (!empty($validated['password'])) {
            $dataToSave['password'] = Hash::make($validated['password']);
        }

        // Logika untuk upload foto
        if ($this->foto) {
            if ($this->isEditMode && $this->user->foto) {
                Storage::delete($this->user->foto);
            }
            $dataToSave['foto'] = $this->foto->store('user-fotos');
        } elseif ($this->fotoRemoved && $this->isEditMode) {
            if ($this->user->foto) {
                Storage::delete($this->user->foto);
            }
            $dataToSave['foto'] = null;
        }

        if ($this->isEditMode) {
            $this->user->update($dataToSave);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'User berhasil diperbarui.', 'icon' => 'success']);
        } else {
            User::create($dataToSave);
            $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'User berhasil ditambahkan.', 'icon' => 'success']);
        }

        $this->closeModal();
    }

    // Metode untuk menghapus user
    #[On('delete-confirmed')]
    public function delete(string $id): void
    {
        // Otorisasi: Jangan biarkan admin menghapus dirinya sendiri
        if ($id === Auth::user()->id) {
            $this->dispatch('swal', ['title' => 'Gagal!', 'text' => 'Anda tidak dapat menghapus akun Anda sendiri.', 'icon' => 'error']);
            return;
        }

        $user = User::find($id);
        if ($user) {
            if ($user->foto) {
                Storage::delete($user->foto);
            }
            $user->delete();
            $this->dispatch('swal', ['title' => 'Dihapus!', 'text' => "User {$user->nama} berhasil dihapus.", 'icon' => 'success']);
        }
    }

    public function closeModal(): void
    {
        $this->userModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['nama', 'email', 'password', 'password_confirmation', 'role', 'jk', 'foto', 'user', 'isEditMode', 'existingFoto', 'fotoRemoved']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => $this->users(),
            'headers' => $this->headers
        ]);
    }
}
