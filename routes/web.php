<?php

use App\Http\Controllers\FileController;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Dashboard;
use App\Livewire\Sekolah\Index as SekolahIndex;
use App\Livewire\Kelas\Index as KelasIndex;
use App\Livewire\Users\Index as UserIndex;
use App\Livewire\Siswa\Manage as SiswaManage;
use App\Livewire\Ujian\Index as UjianIndex;
use App\Livewire\Ujian\SoalManager;
use App\Livewire\Ujian\Daftar as UjianDaftar;
use App\Livewire\Ujian\Pengerjaan as UjianPengerjaan;
use App\Livewire\Ujian\Hasil as UjianHasil;
use App\Livewire\KuisMenjodohkan\Index as KuisMenjodohkanIndex;
use App\Livewire\KuisMenjodohkan\ItemManager as KuisItemManager;
use App\Livewire\KuisMenjodohkan\Pengerjaan as KuisPengerjaan;
use App\Livewire\KuisMenjodohkan\Daftar as KuisDaftar;
use App\Livewire\KuisMenjodohkan\Hasil as KuisHasil;
use Illuminate\Support\Facades\Route;

// Halaman landing page publik
Route::get('/', function () {
    return view('welcome');
});

// Grup rute untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
});

Route::get('/files/sekolah/{sekolahId}/logo', [FileController::class, 'showSekolahLogo'])->name('files.sekolah.logo');
Route::get('/files/user/{userId}/foto', [FileController::class, 'showUserFoto'])->name('files.user.foto');
Route::get('/files/soal/{soalId}/gambar', [FileController::class, 'showSoalImage'])->name('files.soal.gambar');
Route::get('/files/kuis/pertanyaan/{itemPertanyaanId}/gambar', [FileController::class, 'showItemPertanyaanImage'])->name('kuis.item-pertanyaan.gambar');
Route::get('/files/kuis/jawaban/{itemJawabanId}/gambar', [FileController::class, 'showItemJawabanImage'])->name('kuis.item-jawaban.gambar');

// Grup rute untuk pengguna yang sudah terotentikasi
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/sekolah', SekolahIndex::class)->name('sekolah.index')->middleware('role:Admin');
    Route::get('/kelas', KelasIndex::class)->name('kelas.index')->middleware('role:Admin');
    Route::get('/users', UserIndex::class)->name('users.index')->middleware('role:Admin');
    Route::get('/siswa-per-kelas', SiswaManage::class)->name('siswa.manage')->middleware('role:Admin,Guru');
    Route::get('/manajemen-ujian', UjianIndex::class)->name('ujian.index')->middleware('role:Admin,Guru');
    Route::get('/manajemen-ujian/{ujian}/soal', SoalManager::class)
        ->name('ujian.soal.index')
        ->middleware('role:Admin,Guru');
    Route::get('/hasil-ujian', UjianHasil::class)->name('ujian.hasil');
    Route::get('/manajemen-kuis', KuisMenjodohkanIndex::class)->name('kuis.index')->middleware('role:Admin,Guru');
    Route::get('/manajemen-kuis/{kuisMenjodohkan}/items', KuisItemManager::class)
        ->name('kuis.items.index')
        ->middleware('role:Admin,Guru');
    Route::get('/hasil-kuis', KuisHasil::class)->name('kuis.hasil');
});

Route::middleware(['auth', 'role:Siswa'])->group(function () {
    Route::get('/ujian', UjianDaftar::class)->name('ujian.list');
    Route::get('/kerjakan-ujian/{ujian}', UjianPengerjaan::class)->name('ujian.kerjakan');
    Route::get('/kuis', KuisDaftar::class)->name('kuis.list');
    Route::get('/kerjakan-kuis/{kuisMenjodohkan}', KuisPengerjaan::class)->name('kuis.kerjakan');
});
