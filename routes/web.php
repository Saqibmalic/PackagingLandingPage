<?php

use App\Http\Controllers\Dashboard\ArtworkController;
use App\Http\Controllers\Dashboard\ExportController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'landing'])->name('home');
Route::get('/thank-you', [PageController::class, 'thankYou'])->name('thank-you');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

/*
|--------------------------------------------------------------------------
| Leads dashboard
|--------------------------------------------------------------------------
|
| Everything here holds customer contact details, so nothing is reachable
| without signing in — including the CSV exports and the artwork downloads.
|
*/

Route::view('/dashboard/login', 'pages.dashboard.login')
    ->middleware('guest')
    ->name('dashboard.login');

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::view('/', 'pages.dashboard.index')->name('dashboard');
    Route::get('/export', ExportController::class)->name('dashboard.export');
    Route::get('/artwork/{lead}/{file}', ArtworkController::class)->name('dashboard.artwork');

    Route::post('/logout', function () {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('dashboard.login');
    })->name('dashboard.logout');
});
