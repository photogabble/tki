<?php

use Tki\Http\Controllers\HomeController;
use Tki\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
});

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {

    /** @var \Tki\Models\User $user */
    $user = $request->user();

    $user->load(['ship', 'ship.sector', 'ship.sector.links', 'ship.sector.zone']);

    // TODO: Pass loaded user to dashboard...

    // This is akin to main.php
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
