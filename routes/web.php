<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SignageController;
use App\Http\Controllers\Auth\LoginController;

// ----------------------------------------------------------------
// GIRIS / KAYIT / SIFRE ISLEMLERI
// Bu rotalar oturum acmadan da erisilebilir olmali, bu yuzden
// asagidaki "auth.simple" middleware grubunun disinda tutuluyor.
// ----------------------------------------------------------------
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->name('register.submit');

Route::get('/forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.forgot');
Route::post('/forgot-password', [LoginController::class, 'verifyForgotPassword'])->name('password.forgot.submit');
Route::get('/reset-password', [LoginController::class, 'showResetPasswordForm'])->name('password.reset-form');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.reset');

// ----------------------------------------------------------------
// GIRIS GEREKTIREN SAYFALAR
// "auth.simple" middleware'i (app/Http/Middleware/EnsureUserIsLoggedIn.php)
// oturumda gecerli bir kullanici olup olmadigini kontrol eder.
// ----------------------------------------------------------------
Route::middleware('auth.simple')->group(function () {
    // Giris sonrasi ilk gorulen karsilama (ana sayfa) ekrani.
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Route::resource, odalar icin index/create/store/edit/update/destroy
    // rotalarinin hepsini tek satirda otomatik olusturur.
    Route::resource('rooms', RoomController::class);
    Route::post('/rooms/{room}/mark-faulty', [RoomController::class, 'markFaulty'])->name('rooms.mark-faulty');
    Route::post('/rooms/{room}/clear-faulty', [RoomController::class, 'clearFaulty'])->name('rooms.clear-faulty');

    // Ayni sekilde toplantilar icin de standart CRUD rotalari.
    Route::resource('meetings', MeetingController::class);
});

// ----------------------------------------------------------------
// BRIFING SALONU KAPI TABELASI (SIGNAGE)
// Bilincli olarak korumasiz - TV ekraninda sifresiz acik kalmasi gerekiyor.
// ----------------------------------------------------------------
Route::get('/signage/{room}', [SignageController::class, 'show'])->name('signage.show');

// Ana sayfaya girildiginde karsilama ekranina yonlendir.
// (Giris yapilmamissa "auth.simple" middleware'i zaten /login'e gonderecektir.)
Route::get('/', function () {
    return redirect()->route('home');
});
