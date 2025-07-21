<?php

namespace App\Livewire\Ujian;

use App\Models\Soal;
use App\Models\Ujian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SoalForm extends Component
{
    use WithFileUploads;

    public bool $soalModal = false;
    public bool $isEditMode = false;

    public ?Ujian $ujian = null;
    public ?Soal $soal = null;

    // Properti untuk field form
    public string $pertanyaan = '';
    public $gambar_soal;
    public ?string $existingGambarSoal = null;

    public array $opsi = [];

    #[Rule('required|integer|min:0|max:3', message: ['min' => 'Anda harus memilih satu jawaban yang benar.'])]
    public int $jawaban_benar_index = -1;

    public function mount()
    {
        $this->resetForm(); // Inisialisasi form dengan keadaan default
    }

    // Listener untuk membuka modal
    #[On('open-soal-form')]
    public function openModal(string $ujianId, ?string $soalId = null): void
    {
        $this->resetForm();
        $this->ujian = Ujian::find($ujianId);

        if ($soalId) {
            // Mode Edit
            $this->isEditMode = true;
            $soal = Soal::with('opsiJawabans')->find($soalId);
            if ($soal) {
                $this->soal = $soal;
                $this->pertanyaan = $soal->pertanyaan;
                $this->existingGambarSoal = $soal->gambar_soal;

                $opsiFromDb = $soal->opsiJawabans->map(function ($opsi, $index) {
                    if ($opsi->is_benar) {
                        $this->jawaban_benar_index = $index;
                    }
                    return ['teks' => $opsi->teks_opsi];
                })->toArray();

                $this->opsi = $opsiFromDb;
            }
        } else {
            // Mode Create
            $this->isEditMode = false;
        }

        $this->soalModal = true;
    }

    #[Computed]
    public function gambarSoalPreviewUrl(): ?string
    {
        // Prioritas 1: Tampilkan file baru yang sedang di-upload
        if ($this->gambar_soal) {
            try {
                return $this->gambar_soal->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }

        // Prioritas 2: Tampilkan gambar yang sudah ada saat mode edit
        if ($this->isEditMode && $this->existingGambarSoal) {
            return route('files.soal.gambar', ['soalId' => $this->soal->id]);
        }

        // Default: tidak ada preview
        return null;
    }

    public function removeGambarSoal(): void
    {
        $this->gambar_soal = null;
        $this->existingGambarSoal = null; // Hapus juga referensi gambar lama
    }

    public function save(): void
    {
        if (!$this->ujian) return;

        $this->validate(); // Memvalidasi properti dengan atribut Rule
        $this->validate([
            'pertanyaan' => 'required|string',
            'opsi.*.teks' => 'required|string',
        ]);

        DB::transaction(function () {
            // Atur is_benar sebelum menyimpan
            $opsiToSave = [];
            foreach ($this->opsi as $index => $opsiData) {
                $opsiToSave[] = [
                    // Kunci 'teks_opsi' sekarang cocok dengan nama kolom database
                    'teks_opsi' => $opsiData['teks'],
                    'is_benar' => ($index == $this->jawaban_benar_index)
                ];
            }

            $dataSoal = ['pertanyaan' => $this->pertanyaan];
            if ($this->gambar_soal) {
                if ($this->isEditMode && $this->soal->gambar_soal) {
                    Storage::delete($this->soal->gambar_soal);
                }
                $dataSoal['gambar_soal'] = $this->gambar_soal->store('soal-images');
            }
            // Jika gambar dihapus dari UI
            elseif ($this->isEditMode && !$this->existingGambarSoal && $this->soal->gambar_soal) {
                Storage::delete($this->soal->gambar_soal);
                $dataSoal['gambar_soal'] = null;
            }

            if ($this->isEditMode) {
                $this->soal->update($dataSoal);
                $this->soal->opsiJawabans()->delete(); // Hapus opsi lama 
                $this->soal->opsiJawabans()->createMany($opsiToSave); // Buat ulang
            } else {
                $soal = $this->ujian->soals()->create($dataSoal);
                $soal->opsiJawabans()->createMany($opsiToSave);
            }
        });

        $this->closeModal();
        $this->dispatch('swal', ['title' => 'Berhasil!', 'text' => 'Soal berhasil disimpan.', 'icon' => 'success']);
        $this->dispatch('soal-saved'); // Kirim event ke komponen induk untuk refresh
    }

    public function closeModal(): void
    {
        $this->soalModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(); // Livewire akan mereset semua properti ke state awal
        $this->opsi = [
            ['teks' => ''],
            ['teks' => ''],
            ['teks' => ''],
            ['teks' => ''],
        ];
    }

    public function render()
    {
        return view('livewire.ujian.soal-form');
    }
}
