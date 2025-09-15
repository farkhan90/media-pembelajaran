<?php

namespace App\Livewire\Ujian;

use App\Models\HistoriUjian;
use App\Models\Kelas;
use App\Models\Sekolah;
use App\Models\Ujian;
use App\Models\User;
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

    // Properti untuk Modal Rincian
    public ?HistoriUjian $selectedHistori = null;
    public bool $detailModal = false;
    public bool $bantuanModal = false;
    public Collection $detailJawaban;

    // Properti Tabel
    public string $search = '';
    public array $headers;

    public function mount()
    {
        // Setup header default untuk Admin/Guru
        $this->headers = [
            ['key' => 'no', 'label' => 'No.', 'class' => 'w-1'],
            ['key' => 'user.nama', 'label' => 'Nama Siswa'],
            ['key' => 'ujian.judul', 'label' => 'Ujian yang Dikerjakan'],
            ['key' => 'user.kelas_info', 'label' => 'Kelas / Sekolah'],
            ['key' => 'skor_akhir', 'label' => 'Skor', 'class' => 'w-24 text-center'],
        ];

        // Sembunyikan kolom kelas/sekolah untuk Guru
        if (Auth::user()->role === 'Guru') {
            unset($this->headers[3]);
        }

        // Setup header untuk Siswa
        if (Auth::user()->role === 'Siswa') {
            $this->headers = [
                ['key' => 'no', 'label' => 'No.'],
                ['key' => 'ujian.judul', 'label' => 'Judul Ujian'],
                ['key' => 'skor_akhir', 'label' => 'Skor'],
                ['key' => 'waktu_selesai', 'label' => 'Tanggal Selesai'],
            ];
        }
    }
    #[Computed]
    public function ujianOptions()
    {
        return Ujian::where('status', 'Published')->orderBy('judul')->get();
    }

    // Query utama untuk mendapatkan hasil ujian
    #[Computed]
    public function hasilUjians()
    {
        $user = Auth::user();

        $query = HistoriUjian::query()
            ->with(['user.kelas.sekolah', 'ujian'])
            ->where('status', '!=', 'Mengerjakan');

        // Logika otorisasi
        if ($user->role === 'Siswa') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'Guru') {
            // Dapatkan ID siswa dari kelas yang diampu guru
            $siswaIds = User::whereHas('kelas', function ($q) use ($user) {
                $q->whereIn('kelas.id', $user->kelasDiampu->pluck('id'));
            })->pluck('users.id');

            // Filter histori berdasarkan ID siswa tersebut
            $query->whereIn('user_id', $siswaIds);
        }
        // Untuk Admin, tidak ada filter tambahan, semua akan diambil.

        // Filter pencarian
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', fn($sq) => $sq->where('nama', 'like', "%{$this->search}%"))
                    ->orWhereHas('ujian', fn($sq) => $sq->where('judul', 'like', "%{$this->search}%"));
            });
        }

        return $query->orderBy('waktu_selesai', 'desc')->paginate(20);
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
