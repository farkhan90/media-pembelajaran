<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User Admin
        User::create([
            'nama' => 'Admin Utama',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password'),
            'role' => 'Admin',
            'jk' => 'L'
        ]);

        // Contoh User Guru
        User::create([
            'nama' => 'Budi Guru',
            'email' => 'guru@mail.com',
            'password' => Hash::make('password'),
            'role' => 'Guru',
            'jk' => 'L'
        ]);

        // Contoh User Siswa
        User::create([
            'nama' => 'Siti Siswa',
            'email' => 'siswa@mail.com',
            'password' => Hash::make('password'),
            'role' => 'Siswa',
            'jk' => 'P'
        ]);
    }
}
