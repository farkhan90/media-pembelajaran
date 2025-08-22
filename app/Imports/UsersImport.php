<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // 1. Siapkan data dasar yang akan selalu ada
        $dataToSave = [
            'nama' => $row['nama'],
            'email' => $row['email'],
            'role' => $row['role'],
            'jk' => $row['jk'],
        ];

        // 2. Cari user terlebih dahulu untuk menentukan apakah ini update atau create
        $user = User::where('email', $row['email'])->first();

        // 3. Logika untuk Password
        if (!empty($row['password'])) {
            // Jika password diisi di Excel, selalu hash dan tambahkan ke data
            $dataToSave['password'] = Hash::make($row['password']);
        } elseif (!$user) {
            // JIKA INI USER BARU (karena $user adalah null) dan password kosong,
            // buat password default.
            $dataToSave['password'] = Hash::make('password'); // Password default 'password'
        }
        // Jika ini adalah user LAMA ($user ada) dan password kosong,
        // kita tidak melakukan apa-apa (password tidak akan diubah).

        // 4. Jalankan updateOrCreate dengan semua data yang sudah disiapkan
        return User::updateOrCreate(
            ['email' => $row['email']], // Kunci untuk mencari
            $dataToSave                  // Semua data untuk di-create atau di-update
        );
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        // Aturan validasi untuk setiap baris di Excel
        return [
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'nullable|min:8',
            'role' => 'required|in:Admin,Guru,Siswa',
            'jk' => 'required|in:L,P',

            // Validasi unik email ditangani oleh updateOrCreate, 
            // jadi kita tidak perlu 'unique' di sini.
        ];
    }
}
