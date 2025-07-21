<?php

namespace App\Livewire\Ujian;

use App\Models\HistoriUjian;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Ujian;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Hasil extends Component
{
    use WithPagination;

    // Properti untuk Filter (hanya untuk Admin/Guru)
    public ?string $sekolahId = null;
    public ?string $kelasId = null;
    public ?string $ujianId = null;

    // Properti untuk Modal Rincian
    public ?HistoriUjian $selectedHistori = null;
    public bool $detailModal = false;
    public Collection $detailJawaban;

    // Properti Tabel
    public string $search = '';
    public array $headers;

    public function mount()
    {
        $this->headers = [
            ['key' => 'no', 'label' => 'No.', 'class' => 'w-1'],
            ['key' => 'user.nama', 'label' => 'Nama Siswa'],
            ['key' => 'skor_akhir', 'label' => 'Skor', 'class' => 'w-24 text-center'],
            ['key' => 'waktu_mulai', 'label' => 'Waktu Mulai'],
            ['key' => 'waktu_selesai', 'label' => 'Waktu Selesai'],
        ];

        // Jika yang login adalah siswa, tidak perlu filter, langsung isi data
        if (Auth::user()->role === 'Siswa') {
            $this->headers = [
                ['key' => 'no', 'label' => 'No.', 'class' => 'w-1'],
                ['key' => 'ujian.judul', 'label' => 'Judul Ujian'],
                ['key' => 'skor_akhir', 'label' => 'Skor', 'class' => 'w-24 text-center'],
                ['key' => 'waktu_selesai', 'label' => 'Tanggal Selesai'],
            ];
        }
    }

    // Opsi filter disesuaikan dengan role
    #[Computed(cache: true)]
    public function sekolahOptions()
    {
        $user = Auth::user();

        if ($user->role === 'Guru') {
            return Sekolah::whereHas('kelas', function ($query) use ($user) {
                $query->where('guru_pengampu_id', $user->id);
            })->distinct()->orderBy('nama')->get();
        }

        // Untuk Admin (dan role lain jika ada), selalu kembalikan semua sekolah
        return Sekolah::orderBy('nama')->get();
    }

    #[Computed(cache: true)]
    public function kelasOptions()
    {
        // Jika tidak ada sekolah yang dipilih, langsung kembalikan koleksi kosong.
        if (!$this->sekolahId) {
            return collect();
        }

        $user = Auth::user();
        $query = Kelas::where('sekolah_id', $this->sekolahId);

        if ($user->role === 'Guru') {
            $query->where('guru_pengampu_id', $user->id);
        }

        return $query->orderBy('nama')->get();
    }

    #[Computed]
    public function ujianOptions()
    {
        if (!$this->kelasId) {
            return collect();
        }
        return Ujian::where('kelas_id', $this->kelasId)->get(['id', 'judul']);
    }

    // Query utama untuk mendapatkan hasil ujian
    #[Computed]
    public function hasilUjians()
    {
        $user = Auth::user();
        $query = HistoriUjian::query()
            ->with(['user', 'ujian'])
            ->where('status', '!=', 'Mengerjakan');

        if ($user->role === 'Siswa') {
            $query->where('user_id', $user->id);
        } else {
            // Untuk Admin dan Guru, ujian HARUS dipilih
            if (!$this->ujianId) {
                return HistoriUjian::where('id', false)->paginate(15); // Query yang dijamin kosong
            }
            $query->where('ujian_id', $this->ujianId);
        }

        if ($this->search) {
            $query->whereHas('user', fn($q) => $q->where('nama', 'like', "%{$this->search}%"));
        }

        return $query->orderBy('waktu_selesai', 'desc')->paginate(15);
    }

    // Dipanggil saat filter diubah
    public function updated($property)
    {
        if (in_array($property, ['sekolahId', 'kelasId', 'ujianId'])) {
            $this->resetPage();
        }
    }

    // Membuka modal rincian
    public function lihatRincian(string $historiId)
    {
        $this->selectedHistori = HistoriUjian::with([
            'ujian.soals' => function ($query) {
                // Eager load semua yang dibutuhkan untuk rincian
                $query->with(['opsiJawabans']);
            },
            'jawabanSiswas'
        ])->find($historiId);

        if (!$this->selectedHistori) return;

        // Siapkan data untuk ditampilkan di modal
        $jawabanSiswaMap = $this->selectedHistori->jawabanSiswas->keyBy('soal_id');

        $this->detailJawaban = $this->selectedHistori->ujian->soals->map(function ($soal) use ($jawabanSiswaMap) {
            $jawaban = $jawabanSiswaMap->get($soal->id);
            $soal->jawaban_siswa_opsi_id = $jawaban->opsi_jawaban_id ?? null;
            return $soal;
        });

        $this->detailModal = true;
    }

    public function render()
    {
        return view('livewire.ujian.hasil');
    }
}
