<?php

namespace App\Livewire\Pembelajaran;

use App\Models\ProgresPulauSiswa;
use App\Models\SiswaPerkelas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class RefleksiPage extends Component
{
    public string $pulau;
    public string $judul;
    public array $daftarPertanyaan = [];
    public ?string $pulauBerikutnya = 'papua'; // Hardcode untuk Sulawesi

    #[Rule('required', message: 'Jangan lupa diisi ya jawabanmu!')]
    #[Rule('min:20', message: 'Jawabanmu masih terlalu pendek nih, coba tulis lebih banyak lagi ya!')]
    public string $jawaban = '';

    // Urutan pulau untuk validasi progres
    protected array $urutanPulau = ['sumatera', 'jawa', 'kalimantan', 'sulawesi', 'papua'];

    public function mount(string $pulau)
    {
        if ($pulau !== 'sulawesi') {
            abort(404);
        }

        $this->pulau = $pulau;
        $this->judul = "Refleksi & Aksi Petualangan";
        $this->daftarPertanyaan = [
            1 => "Coba ingat lagi saat JoJo dan Mika berdebat. Bagaimana ya, perasaan JoJo dan Mika saat itu? ",
            2 => "Video ini menunjukkan bahwa Indonesia punya banyak budaya yang indah. Apa satu hal yang kamu pelajari tentang budaya yang berbeda dari video ini? Mengapa penting bagi kita untuk tahu dan menghargai perbedaan itu?",
            3 => "Setelah menonton video ini, apa satu janji kecil yang bisa kamu ucapkan di dalam hati untuk membuat lingkungan sekolahmu jadi tempat yang lebih nyaman dan penuh persahabatan bagi semua orang?",
            4 => "Sekarang coba selesaikan permasalahan ini! <br>
                    Ibu Guru memberikan tugas kelompok yang seru. Setiap kelompok harus membuat video pendek di mana setiap anggota memperkenalkan diri dan mengucapkan satu kalimat sederhana dari bahasa daerahnya. Kamu melihat salah satu teman kelompokmu tampak cemas. Dia berbisik padamu kalau ia malu menggunakan bahasa daerahnya di depan video. Ia khawatir teman-teman lain tidak mengerti dan menertawakannya karena bahasa daerahnya tidak sepopuler bahasa daerah lain. Ceritakan apa yang akan kamu lakukan untuk membantu temanmu agar ia merasa bangga dan berani menggunakan bahasa daerahnya di dalam video kelompok kalian!
                    "
        ];

        $user = Auth::user();

        // Otorisasi: Siswa hanya bisa mengakses pulau yang aktif
        if ($user->role === 'Siswa') {
            $progresSiswa = ProgresPulauSiswa::where('user_id', $user->id)->pluck('nama_pulau')->toArray();

            // Cek dulu apakah siswa sudah tamat
            $sudahTamat = in_array('papua', $progresSiswa);

            if (!$sudahTamat) {
                // Jika belum tamat, jalankan logika otorisasi linear
                $progresTerakhir = $this->getProgresTerakhir($progresSiswa);
                $progresIndex = is_null($progresTerakhir) ? -1 : array_search($progresTerakhir, $this->urutanPulau);
                $pulauAktif = $this->urutanPulau[$progresIndex + 1] ?? null;

                if ($pulau !== $pulauAktif) {
                    session()->flash('pesan_error', 'Selesaikan pulau sebelumnya dulu ya!');
                    $this->redirect(route('peta-petualangan'), navigate: true);
                    return;
                }
            }
            // Jika sudah tamat, tidak ada pengecekan lebih lanjut, akses diizinkan.
        }
    }

    /**
     * Menandai pulau ini sebagai selesai dan menyimpan jawaban refleksi.
     */
    public function tandaiSelesai()
    {
        // Validasi properti 'jawaban'
        $this->validate();

        $user = Auth::user();

        if ($user->role === 'Siswa') {

            ProgresPulauSiswa::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'nama_pulau' => $this->pulau,
                ],
                [
                    'waktu_selesai' => now(),
                    'jawaban_refleksi' => $this->jawaban // <-- Simpan jawaban
                ]
            );
        }

        return $this->redirect(route('peta-petualangan'), navigate: true);
    }

    private function getProgresTerakhir(array $progres): ?string
    {
        $lastProgress = null;
        foreach ($this->urutanPulau as $pulau) {
            if (in_array($pulau, $progres)) {
                $lastProgress = $pulau;
            } else {
                break;
            }
        }
        return $lastProgress;
    }

    public function render()
    {
        return view('livewire.pembelajaran.refleksi-page');
    }
}
