<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\HistoriKuis;
use App\Models\Kelas;
use App\Models\KuisMenjodohkan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
    public ?HistoriKuis $selectedHistori = null;
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
            ['key' => 'kuis.judul', 'label' => 'Kuis yang Dikerjakan'],
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
                ['key' => 'kuis.judul', 'label' => 'Judul Kuis'],
                ['key' => 'skor_akhir', 'label' => 'Skor'],
                ['key' => 'waktu_selesai', 'label' => 'Tanggal Selesai'],
            ];
        }
    }

    // Query utama untuk mendapatkan hasil kuis
    #[Computed]
    public function hasilKuis()
    {
        $user = Auth::user();

        $query = HistoriKuis::query()
            ->with(['user.kelas.sekolah', 'kuis'])
            ->where('status', 'Selesai');

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
        // Admin akan melihat semua hasil

        // Filter pencarian
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', fn($sq) => $sq->where('nama', 'like', "%{$this->search}%"))
                    ->orWhereHas('kuis', fn($sq) => $sq->where('judul', 'like', "%{$this->search}%"));
            });
        }

        return $query->orderBy('waktu_selesai', 'desc')->paginate(20);
    }

    public function updated($property)
    {
        if (in_array($property, ['sekolahId', 'kelasId', 'kuisId'])) {
            $this->resetPage();
        }
    }

    public function lihatRincian(string $historiId)
    {
        $this->selectedHistori = HistoriKuis::with([
            'kuis.itemPertanyaans.itemJawaban', // Eager load semua yang dibutuhkan
            'jawabanJodohSiswas'
        ])->find($historiId);

        if (!$this->selectedHistori) return;

        // Siapkan data untuk ditampilkan di modal
        $jawabanSiswaMap = $this->selectedHistori->jawabanJodohSiswas->keyBy('item_pertanyaan_id');

        $this->detailJawaban = $this->selectedHistori->kuis->itemPertanyaans->map(function ($itemPertanyaan) use ($jawabanSiswaMap) {
            $jawaban = $jawabanSiswaMap->get($itemPertanyaan->id);
            // Tambahkan properti dinamis ke objek item pertanyaan
            $itemPertanyaan->jawaban_siswa_item_jawaban_id = $jawaban->item_jawaban_id ?? null;
            return $itemPertanyaan;
        });

        $this->detailModal = true;
    }

    public function render()
    {
        return view('livewire.kuis-menjodohkan.hasil');
    }
}
