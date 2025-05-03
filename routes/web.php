<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\QRController;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/welcome', function () {
    if (!session('user_id')) {
        return redirect('/login');
    }
    return view('welcome');
})->name('welcome');

// Other Routes
Route::get('/msg', function () {
    return view('whatsapp');
});

Route::post('/send-bulk', [MessageController::class, 'sendBulkFromExcelWithImage'])->name('send.bulk');

// Proxy route for QR
Route::get('/get-qr', function (Illuminate\Http\Request $request) {
    $session = $request->query('session');

    if (!$session) {
        return response()->json(['error' => 'Session required'], 400);
    }

    $response = Http::get('http://localhost:4000/get-qr', [
        'session' => $session
    ]);

    return $response->json();
});

Route::get('/qr', [QRController::class, 'index']);
Route::get('/get-qr', [QRController::class, 'getQr']);
Route::get('/logout', [QRController::class, 'logout']);
Route::get('/sessions', [MessageController::class, 'getAllActiveSessions'])->name('sessions.list');

