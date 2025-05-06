<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\QRController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Welcome page (protected)
Route::get('/welcome', function () {
    if (!session('user_id')) {
        return redirect('/login');
    }
    return view('welcome');
})->name('welcome');

// WhatsApp message UI
Route::get('/msg', function () {
    return view('whatsapp');
});

// Send bulk messages from Excel with image
Route::post('/send-bulk', [MessageController::class, 'sendBulkFromExcelWithImage'])->name('send.bulk');

// QR Code Management
Route::get('/qr', [QRController::class, 'index']);
Route::get('/get-qr', [QRController::class, 'getQr']);
Route::get('/logout', [QRController::class, 'logout']);

// Active WhatsApp sessions
Route::get('/sessions', [MessageController::class, 'getAllActiveSessions'])->name('sessions.list');
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});
