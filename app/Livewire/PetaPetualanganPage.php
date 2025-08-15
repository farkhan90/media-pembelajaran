<?php

namespace App\Livewire;

use App\Models\ProgresPulauSiswa;
use App\Models\SiswaPerkelas;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class PetaPetualanganPage extends Component
{
    public array $progresSiswa = [];
    protected array $urutanPulau = ['sumatera', 'jawa', 'kalimantan', 'sulawesi', 'papua'];

    public function mount()
    {
        if (Auth::user()->role === 'Siswa') {
            $this->progresSiswa = ProgresPulauSiswa::where('user_id', Auth::id())
                ->pluck('nama_pulau')
                ->toArray();
        }
    }

    #[Computed]
    public function pulauStatus(): array
    {
        if (in_array(Auth::user()->role, ['Admin', 'Guru']) || in_array('papua', $this->progresSiswa)) {
            return array_fill_keys($this->urutanPulau, 'terbuka');
        }

        $status = [];
        $pulauAktifDitemukan = false;

        foreach ($this->urutanPulau as $pulau) {
            if (in_array($pulau, $this->progresSiswa)) {
                $status[$pulau] = 'selesai';
            } elseif (!$pulauAktifDitemukan) {
                $status[$pulau] = 'aktif';
                $pulauAktifDitemukan = true;
            } else {
                $status[$pulau] = 'terkunci';
            }
        }
        return $status;
    }

    // Helper untuk membuat link
    public function getLinkForPulau(string $pulau): string
    {
        if ($pulau === 'papua') {
            if (Auth::user()->role === 'Siswa') {
                return route('penilaian.runner', ['pulau' => 'papua']);
            } else {
                return route('penilaian.laporan', ['pulau' => 'papua']);
            }
        }
        $routes = [
            'sumatera'   => route('pembelajaran.video', ['pulau' => 'sumatera']),
            'jawa'       => route('pembelajaran.materi', ['pulau' => 'jawa']),
            'kalimantan' => route('pembelajaran.video', ['pulau' => 'kalimantan']),
            'sulawesi'   => route('pembelajaran.refleksi', ['pulau' => 'sulawesi']),
            // 'papua'      => route('penilaian.runner', ['pulau' => 'papua'])
        ];
        return $routes[$pulau] ?? '#';
    }

    #[Computed]
    public function pulauData(): array
    {
        $statusPulau = $this->pulauStatus();

        return [
            ['id' => 'sumatera', 'nama' => 'Sumatera', 'posisi' => 'top: 0%; left: 0%;', 'lebar' => 'w-[29.5%]', 'status' => $statusPulau['sumatera'], 'warna' => 'primary', 'posisipin' => 'top: 40%; left: 50%;'],
            ['id' => 'jawa', 'nama' => 'Jawa', 'posisi' => 'top: 69%; left: 23%;', 'lebar' => 'w-[58.5%]', 'status' => $statusPulau['jawa'], 'warna' => 'secondary', 'posisipin' => 'top: 0%; left: 20%;'],
            ['id' => 'kalimantan', 'nama' => 'Kalimantan', 'posisi' => 'top: 7%; left: 30.5%;', 'lebar' => 'w-[23%]', 'status' => $statusPulau['kalimantan'], 'warna' => 'accent', 'posisipin' => 'top: 30%; left: 45%;'],
            ['id' => 'sulawesi', 'nama' => 'Sulawesi', 'posisi' => 'top: 22.4%; left: 52.5%;', 'lebar' => 'w-[17.5%]', 'status' => $statusPulau['sulawesi'], 'warna' => 'warning', 'posisipin' => 'top: 20%; left: 20%;'],
            ['id' => 'papua', 'nama' => 'Papua', 'posisi' => 'top: 18%; right: 0%', 'lebar' => 'w-[29.5%]', 'status' => $statusPulau['papua'], 'warna' => 'info', 'posisipin' => 'top: 30%; left: 80%;'],
        ];
    }

    public function render()
    {
        return view('livewire.peta-petualangan-page');
    }
}
