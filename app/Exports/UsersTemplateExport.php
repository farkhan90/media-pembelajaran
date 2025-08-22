<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function array(): array
    {
        // Kita tidak perlu data contoh, hanya header. Jadi kembalikan array kosong.
        return [];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ini adalah header kolom yang akan muncul di file Excel
        return [
            'nama',
            'email',
            'password', // Kosongkan jika ingin generate otomatis, isi untuk set manual
            'role',     // Admin, Guru, Siswa
            'jk',       // L atau P
        ];
    }
}
