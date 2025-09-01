<?php

namespace App\Livewire\Auth;

use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.guest')]
class RegisterPage extends Component
{
    use WithFileUploads;

    public string $tabAktif = 'guru';

    // Properti Form Guru
    public string $nama_guru = '';
    public string $email_guru = '';
    public string $password_guru = '';
    public string $password_guru_confirmation = '';
    public string $npsn = '';
    public string $nama_sekolah = '';
    public $logo_sekolah;
    public string $nama_kelas = '';
    public bool $sekolahDitemukan = false;
    public ?string $logoSekolahPreview = null;

    // Properti Form Siswa
    public string $nama_siswa = '';
    public string $email_siswa = '';
    public string $password_siswa = '';
    public string $password_siswa_confirmation = '';
    public string $jk_siswa = 'L';
    public ?string $sekolah_id_siswa = null;
    public ?string $kelas_id_siswa = null;

    public bool $bantuanModal = false;

    // Computed property untuk opsi dropdown
    #[Computed(cache: true)]
    public function sekolahOptions()
    {
        return Sekolah::orderBy('nama')->get(['id', 'nama']);
    }

    #[Computed]
    public function kelasOptions()
    {
        if (!$this->sekolah_id_siswa) return collect();
        return Kelas::where('sekolah_id', $this->sekolah_id_siswa)->orderBy('nama')->get(['id', 'nama']);
    }

    // Lifecycle hook saat NPSN diubah
    public function updatedNpsn($value)
    {
        $sekolah = Sekolah::where('npsn', $value)->first();
        if ($sekolah) {
            $this->sekolahDitemukan = true;
            $this->nama_sekolah = $sekolah->nama;
            $this->logoSekolahPreview = $sekolah->logo ? route('files.sekolah.logo', ['sekolahId' => $sekolah->id]) : null;
        } else {
            $this->sekolahDitemukan = false;
            $this->nama_sekolah = '';
            $this->logoSekolahPreview = null;
        }
    }

    // Lifecycle hook saat Sekolah Siswa diubah
    public function updatedSekolahIdSiswa()
    {
        $this->reset('kelas_id_siswa');
    }

    public function resetPilihanKelasSiswa()
    {
        // Mereset kedua properti terkait
        $this->reset(['sekolah_id_siswa', 'kelas_id_siswa']);
    }

    // Aksi Registrasi Guru
    public function registerGuru()
    {
        $validated = $this->validate([
            'nama_guru' => 'required|string|max:255',
            'email_guru' => 'required|email|max:255|unique:users,email',
            'password_guru' => ['required', 'confirmed', Password::min(8)],
            'npsn' => 'required|string|max:10',
            'nama_sekolah' => 'required|string|max:255',
            'logo_sekolah' => 'nullable|image|max:1024',
            'nama_kelas' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat atau temukan Sekolah
            $sekolah = Sekolah::firstOrCreate(
                ['npsn' => $validated['npsn']],
                ['nama' => $validated['nama_sekolah']]
            );
            // Upload logo hanya jika sekolah baru
            if ($sekolah->wasRecentlyCreated && $this->logo_sekolah) {
                $sekolah->logo = $this->logo_sekolah->store('sekolah-logos');
                $sekolah->save();
            }

            // 2. Buat User Guru
            $guru = User::create([
                'nama' => $validated['nama_guru'],
                'email' => $validated['email_guru'],
                'password' => Hash::make($validated['password_guru']),
                'role' => 'Guru',
                'jk' => 'L', // Default, bisa ditambahkan ke form jika perlu
            ]);

            // 3. Buat Kelas dan hubungkan
            Kelas::create([
                'nama' => $validated['nama_kelas'],
                'sekolah_id' => $sekolah->id,
                'guru_pengampu_id' => $guru->id,
            ]);

            // 4. Login-kan guru
            Auth::login($guru);
        });

        return redirect()->route('selamat-datang');
    }

    // Aksi Registrasi Siswa
    public function registerSiswa()
    {
        $validated = $this->validate([
            'nama_siswa' => 'required|string|max:255',
            'email_siswa' => 'required|email|max:255|unique:users,email',
            'password_siswa' => ['required', 'confirmed', Password::min(8)],
            'jk_siswa' => 'required|in:L,P',
            'sekolah_id_siswa' => 'nullable|exists:sekolahs,id',
            'kelas_id_siswa' => 'nullable|exists:kelas,id',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Buat User Siswa
            $siswa = User::create([
                'nama' => $validated['nama_siswa'],
                'email' => $validated['email_siswa'],
                'password' => Hash::make($validated['password_siswa']),
                'role' => 'Siswa',
                'jk' => $validated['jk_siswa'],
            ]);

            // 2. Jika kelas dipilih, hubungkan
            if (!empty($validated['kelas_id_siswa'])) {
                $siswa->kelas()->attach($validated['kelas_id_siswa']);
            }

            // 3. Login-kan siswa
            Auth::login($siswa);
        });

        return redirect()->route('selamat-datang');
    }


    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
