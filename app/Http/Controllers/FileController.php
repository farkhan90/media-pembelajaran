<?php

namespace App\Http\Controllers;

use App\Models\ItemJawaban;
use App\Models\ItemPertanyaan;
use App\Models\Sekolah;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Mengambil dan menayangkan logo sekolah berdasarkan ID sekolah.
     * Ini memastikan bahwa hanya logo dari sekolah yang ada yang dapat diakses.
     *
     * @param  string $sekolahId
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    public function showSekolahLogo(string $sekolahId)
    {
        $sekolah = Sekolah::findOrFail($sekolahId);

        // Jika sekolah tidak punya logo atau file tidak ditemukan, kembalikan 404.
        if (!$sekolah->logo || !Storage::exists($sekolah->logo)) {
            abort(404, 'Logo tidak ditemukan.');
        }

        // Dapatkan path lengkap ke file
        $path = Storage::path($sekolah->logo);

        // Tentukan tipe MIME dari file
        $mime = mime_content_type($path);

        // Siapkan header untuk respons
        $headers = [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ];

        // Kembalikan file sebagai streamed response
        return Storage::response($sekolah->logo, null, $headers);
    }

    public function showUserFoto(string $userId)
    {
        $user = User::findOrFail($userId);

        // Jika user tidak punya foto atau file tidak ada, tampilkan avatar default.
        if (!$user->foto || !Storage::exists($user->foto)) {
            // Mengarahkan ke file avatar default di folder public
            // Pastikan Anda memiliki file ini di 'public/assets/img/default-avatar.png'
            return response()->file(public_path('assets/img/default-avatar.png'));
        }

        // Jika file ada, tayangkan dengan aman.
        return Storage::response($user->foto);
    }

    public function showSoalImage(string $soalId)
    {
        $soal = Soal::findOrFail($soalId);

        // Jika soal tidak punya gambar atau file fisiknya tidak ada, kembalikan 404.
        if (!$soal->gambar_soal || !Storage::exists($soal->gambar_soal)) {
            // Alternatif: kembalikan gambar placeholder jika ada
            // return response()->file(public_path('assets/img/placeholder-image.png'));
            abort(404, 'Gambar tidak ditemukan.');
        }

        // Tayangkan file dengan aman dari storage.
        return Storage::response($soal->gambar_soal);
    }

    /**
     * Menayangkan gambar untuk Item Pertanyaan Kuis.
     */
    public function showItemPertanyaanImage(string $itemPertanyaanId)
    {
        $item = ItemPertanyaan::findOrFail($itemPertanyaanId);

        if ($item->tipe_item !== 'Gambar' || !$item->konten || !Storage::exists($item->konten)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        return Storage::response($item->konten);
    }

    /**
     * Menayangkan gambar untuk Item Jawaban Kuis.
     */
    public function showItemJawabanImage(string $itemJawabanId)
    {
        $item = ItemJawaban::findOrFail($itemJawabanId);

        if ($item->tipe_item !== 'Gambar' || !$item->konten || !Storage::exists($item->konten)) {
            abort(404, 'Gambar tidak ditemukan.');
        }

        return Storage::response($item->konten);
    }
}
