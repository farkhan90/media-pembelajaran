<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\HistoriKuis;
use App\Models\Kelas;
use App\Models\KuisMenjodohkan;
use App\Models\Sekolah;
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

    // Properti untuk Filter (hanya untuk Admin/Guru)
    public ?string $sekolahId = null;
    public ?string $kelasId = null;
    public ?string $kuisId = null;

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
        $this->headers = [
            ['key' => 'no', 'label' => 'No.', 'class' => 'w-1'],
            ['key' => 'user.nama', 'label' => 'Nama Siswa'],
            ['key' => 'skor_akhir', 'label' => 'Skor', 'class' => 'w-24 text-center'],
            ['key' => 'waktu_selesai', 'label' => 'Waktu Selesai'],
        ];

        if (Auth::user()->role === 'Siswa') {
            $this->headers = [
                ['key' => 'no', 'label' => 'No.', 'class' => 'w-1'],
                ['key' => 'kuis.judul', 'label' => 'Judul Kuis'],
                ['key' => 'skor_akhir', 'label' => 'Skor', 'class' => 'w-24 text-center'],
                ['key' => 'waktu_selesai', 'label' => 'Tanggal Selesai'],
            ];
        }
    }

    // Opsi filter (logika yang sama dengan komponen lain)
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
    public function kuisOptions()
    {
        if (!$this->kelasId) {
            return collect();
        }
        return KuisMenjodohkan::where('kelas_id', $this->kelasId)->get(['id', 'judul']);
    }

    // Query utama untuk mendapatkan hasil kuis
    #[Computed]
    public function hasilKuis()
    {
        $user = Auth::user();
        $query = HistoriKuis::query()
            ->with(['user', 'kuis'])
            ->where('status', 'Selesai');

        if ($user->role === 'Siswa') {
            $query->where('user_id', $user->id);
        } else {
            if (!$this->kuisId) {
                return HistoriKuis::where('id', false)->paginate(15);
            }
            $query->where('kuis_id', $this->kuisId);
        }

        if ($this->search && $user->role !== 'Siswa') {
            $query->whereHas('user', fn($q) => $q->where('nama', 'like', "%{$this->search}%"));
        }

        return $query->orderBy('waktu_selesai', 'desc')->paginate(15);
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
