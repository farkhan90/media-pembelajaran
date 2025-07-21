<?php

namespace App\Livewire\KuisMenjodohkan;

use App\Models\HistoriKuis;
use App\Models\ItemJawaban;
use App\Models\KuisMenjodohkan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.ujian-layout')]
class Pengerjaan extends Component
{
    public KuisMenjodohkan $kuis;
    public HistoriKuis $histori;

    public Collection $itemPertanyaans;
    public Collection $itemJawabans;

    // State untuk menyimpan pasangan jawaban siswa [item_pertanyaan_id => item_jawaban_id]
    public array $pasanganSiswa = [];

    // State untuk UI klik
    public ?string $selectedPertanyaanId = null;

    public function mount(KuisMenjodohkan $kuisMenjodohkan)
    {
        $this->kuis = $kuisMenjodohkan;
        $user = auth()->user();

        if (!$user->kelas()->whereHas('kuisMenjodohkan', fn($q) => $q->where('id', $this->kuis->id))->exists()) {
            abort(403, 'Anda tidak memiliki akses ke kuis ini.');
        }

        $historiInProgress = HistoriKuis::where('kuis_id', $this->kuis->id)
            ->where('user_id', $user->id)
            ->where('status', 'Mengerjakan')
            ->first();

        if ($historiInProgress) {
            $this->histori = $historiInProgress;
        } else {
            $this->histori = HistoriKuis::create([
                'kuis_id' => $this->kuis->id,
                'user_id' => $user->id,
                'waktu_mulai' => now(),
                'status' => 'Mengerjakan',
            ]);
        }

        $this->loadItems();
        $this->loadJawaban();
    }

    protected function loadItems()
    {
        $urutanPertanyaan = session('urutan_pertanyaan_kuis_' . $this->histori->id);
        $urutanJawaban = session('urutan_jawaban_kuis_' . $this->histori->id);

        $itemPertanyaanIds = $this->kuis->itemPertanyaans()->pluck('id');

        if (!$urutanPertanyaan) {
            $urutanPertanyaan = $itemPertanyaanIds->shuffle()->toArray();
            session(['urutan_pertanyaan_kuis_' . $this->histori->id => $urutanPertanyaan]);
        }

        if (!$urutanJawaban) {
            $urutanJawaban = ItemJawaban::whereIn('item_pertanyaan_id', $itemPertanyaanIds)->pluck('id')->shuffle()->toArray();
            session(['urutan_jawaban_kuis_' . $this->histori->id => $urutanJawaban]);
        }

        $this->itemPertanyaans = \App\Models\ItemPertanyaan::whereIn('id', $urutanPertanyaan)
            ->orderByRaw('FIELD(id, "' . implode('","', $urutanPertanyaan) . '")')
            ->get();

        $this->itemJawabans = ItemJawaban::whereIn('id', $urutanJawaban)
            ->orderByRaw('FIELD(id, "' . implode('","', $urutanJawaban) . '")')
            ->get();
    }

    protected function loadJawaban()
    {
        $this->pasanganSiswa = $this->histori->jawabanJodohSiswas()
            ->pluck('item_jawaban_id', 'item_pertanyaan_id')
            ->toArray();
    }

    public function pilihPertanyaan(string $itemPertanyaanId)
    {
        // Jika item yang sudah dipasangkan diklik, hapus pasangannya
        if (isset($this->pasanganSiswa[$itemPertanyaanId])) {
            $this->hapusPasangan($itemPertanyaanId);
            return;
        }

        if ($this->selectedPertanyaanId === $itemPertanyaanId) {
            $this->selectedPertanyaanId = null;
        } else {
            $this->selectedPertanyaanId = $itemPertanyaanId;
        }
    }

    public function pilihJawaban(string $itemJawabanId)
    {
        // Hanya proses jika ada pertanyaan yang aktif dan jawaban ini belum dipasangkan
        if ($this->selectedPertanyaanId && !in_array($itemJawabanId, $this->pasanganSiswa)) {
            $this->pasanganSiswa[$this->selectedPertanyaanId] = $itemJawabanId;

            $this->histori->jawabanJodohSiswas()->updateOrCreate(
                ['item_pertanyaan_id' => $this->selectedPertanyaanId],
                ['item_jawaban_id' => $itemJawabanId]
            );

            $this->selectedPertanyaanId = null;
            $this->dispatch('pasangan-updated');
        }
    }

    protected function hapusPasangan(string $itemPertanyaanId)
    {
        $this->histori->jawabanJodohSiswas()->where('item_pertanyaan_id', $itemPertanyaanId)->delete();
        unset($this->pasanganSiswa[$itemPertanyaanId]);
        $this->dispatch('pasangan-updated');
    }

    // Metode baru untuk mereset semua jawaban
    public function resetSemuaJawaban()
    {
        $this->histori->jawabanJodohSiswas()->delete();
        $this->pasanganSiswa = [];
        $this->selectedPertanyaanId = null;
        $this->dispatch('pasangan-updated');
    }

    public function selesaikanKuis()
    {
        $jawabanTersimpan = $this->histori->jawabanJodohSiswas;
        $jumlahBenar = 0;

        foreach ($jawabanTersimpan as $jawaban) {
            $jawabanBenar = ItemJawaban::find($jawaban->item_jawaban_id);
            if ($jawabanBenar && $jawabanBenar->item_pertanyaan_id === $jawaban->item_pertanyaan_id) {
                $jumlahBenar++;
            }
        }

        $totalItem = $this->itemPertanyaans->count();
        $skor = ($totalItem > 0) ? ($jumlahBenar / $totalItem) * 100 : 0;

        $this->histori->update([
            'waktu_selesai' => now(),
            'skor_akhir' => $skor,
            'status' => 'Selesai',
        ]);

        session()->forget('urutan_pertanyaan_kuis_' . $this->histori->id);
        session()->forget('urutan_jawaban_kuis_' . $this->histori->id);

        $this->dispatch('kuis-telah-selesai', [
            'title' => 'Kuis Selesai!',
            'text' => 'Skor Anda adalah: ' . round($skor, 2),
            'icon' => 'success',
            'redirectUrl' => route('kuis.list')
        ]);
    }

    public function render()
    {
        return view('livewire.kuis-menjodohkan.pengerjaan');
    }
}
