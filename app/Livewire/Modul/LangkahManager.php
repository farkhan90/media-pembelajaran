<?php

namespace App\Livewire\Modul;

use App\Models\KuisMenjodohkan;
use App\Models\Langkah;
use App\Models\Modul;
use App\Models\Ujian;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class LangkahManager extends Component
{
    use WithFileUploads;

    public Modul $modul;

    // Properti untuk Modal dan Form
    public bool $langkahModal = false;
    public bool $isEditMode = false;
    public ?Langkah $langkah = null;

    // Properti Form
    public string $judul = '';
    public string $tipe = 'video';

    // Izinkan properti ini menjadi null
    public ?string $konten_path = null;
    public ?string $konten_teks = null;

    public ?string $ujian_id = null;
    public ?string $kuis_menjodohkan_id = null;
    public string $kondisi_selesai_tipe = 'otomatis';
    public ?int $kondisi_selesai_detik = null;
    public int $urutan = 1;

    public bool $bantuanModal = false;

    // Properti Tabel
    public array $headers;

    public array $tipeOptions = [
        ['id' => 'video', 'name' => 'Video'],
        ['id' => 'audio', 'name' => 'Audio'],
        ['id' => 'canva', 'name' => 'Embed Canva'],
        ['id' => 'pdf', 'name' => 'Buku Balik (PDF)'],
        ['id' => 'soal_esai', 'name' => 'Soal Esai'],
        ['id' => 'penilaian_akhir', 'name' => 'Penilaian Akhir (Ujian + Kuis)'],
    ];

    public array $kondisiOptions = [
        ['id' => 'otomatis', 'name' => 'Otomatis (Saat video/audio selesai)'],
        ['id' => 'timer', 'name' => 'Timer (Waktu minimum)'],
        ['id' => 'submit_form', 'name' => 'Submit Form (Untuk soal esai)'],
    ];

    public function mount(Modul $modul)
    {
        $this->modul = $modul;

        // Definisikan header baru untuk tabel
        $this->headers = [
            ['key' => 'urutan', 'label' => '#', 'class' => 'w-1 text-center'],
            ['key' => 'judul', 'label' => 'Judul Langkah'],
            ['key' => 'tipe', 'label' => 'Tipe', 'class' => 'w-48 text-center'],
            ['key' => 'detail', 'label' => 'Detail Konten'],
        ];
    }

    #[Computed(cache: true)]
    public function ujianOptions()
    {
        return Ujian::where('status', 'Published')->get(['id', 'judul']);
    }

    #[Computed(cache: true)]
    public function kuisOptions()
    {
        return KuisMenjodohkan::where('status', 'Published')->get(['id', 'judul']);
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->urutan = $this->modul->langkahs()->max('urutan') + 1; // Urutan otomatis
        $this->langkahModal = true;
    }

    public function edit(Langkah $langkah)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->langkah = $langkah;

        $this->judul = $langkah->judul;
        $this->tipe = $langkah->tipe;
        $this->konten_path = $langkah->konten_path;
        $this->konten_teks = $langkah->konten_teks;
        $this->ujian_id = $langkah->ujian_id;
        $this->kuis_menjodohkan_id = $langkah->kuis_menjodohkan_id;
        $this->urutan = $langkah->urutan;

        $kondisi = $langkah->kondisi_selesai ?? [];
        $this->kondisi_selesai_tipe = $kondisi['tipe'] ?? 'otomatis';
        $this->kondisi_selesai_detik = $kondisi['detik'] ?? null;

        $this->langkahModal = true;
    }

    public function save()
    {
        // Validasi dasar
        $validated = $this->validate([
            'judul' => 'required|string|max:255',
            'tipe' => 'required|in:video,audio,canva,pdf,soal_esai,penilaian_akhir',
            'urutan' => 'required|integer',
        ]);

        // Siapkan data kondisional
        $validated['konten_path'] = ($this->tipe === 'video' || $this->tipe === 'audio' || $this->tipe === 'canva' || $this->tipe === 'pdf') ? $this->konten_path : null;
        $validated['konten_teks'] = ($this->tipe === 'soal_esai') ? $this->konten_teks : null;
        $validated['ujian_id'] = ($this->tipe === 'penilaian_akhir') ? $this->ujian_id : null;
        $validated['kuis_menjodohkan_id'] = ($this->tipe === 'penilaian_akhir') ? $this->kuis_menjodohkan_id : null;

        // Siapkan data kondisi selesai
        $kondisi = ['tipe' => $this->kondisi_selesai_tipe];
        if ($this->kondisi_selesai_tipe === 'timer') {
            $this->validate(['kondisi_selesai_detik' => 'required|integer|min:1']);
            $kondisi['detik'] = $this->kondisi_selesai_detik;
        }
        $validated['kondisi_selesai'] = $kondisi;

        if ($this->isEditMode) {
            $this->langkah->update($validated);
        } else {
            $this->modul->langkahs()->create($validated);
        }

        $this->langkahModal = false;
    }

    private function resetForm()
    {
        $this->reset([
            'langkahModal',
            'isEditMode',
            'langkah',
            'judul',
            'tipe',
            'konten_path',
            'konten_teks',
            'ujian_id',
            'kuis_menjodohkan_id',
            'kondisi_selesai_tipe',
            'kondisi_selesai_detik',
            'urutan'
        ]);

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        // Eager load relasi yang akan kita tampilkan di tabel
        $langkahs = $this->modul
            ->langkahs()
            ->with(['ujian', 'kuisMenjodohkan']) // Memuat data ujian dan kuis terkait
            ->paginate(10);

        return view('livewire.modul.langkah-manager', ['langkahs' => $langkahs]);
    }
}
