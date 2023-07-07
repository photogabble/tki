<?php

use Tki\Http\Controllers\EncounterController;
use Tki\Http\Controllers\GameController;
use Tki\Http\Controllers\HomeController;
use Tki\Http\Controllers\NavigationPresetsController;
use Tki\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Tki\Http\Controllers\RankingController;
use Tki\Http\Controllers\RealSpaceNavigationController;
use Tki\Http\Controllers\WarpNavigationController;

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

Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [GameController::class, 'index'])
        ->name('dashboard');
    Route::get('/explore', [GameController::class, 'galaxyMap'])
        ->name('explore');

    Route::group(['prefix' => 'navigation'], function () {
        Route::get('real-space', [RealSpaceNavigationController::class, 'calculateRealSpaceRoute'])
            ->name('real-space.calculate');

        Route::post('real-space', [RealSpaceNavigationController::class, 'makeRealSpaceMove'])
            ->name('real-space.move');

        Route::get('warp', [WarpNavigationController::class, 'calculateWarpMoves'])
            ->name('warp.calculate');

        Route::post('warp', [WarpNavigationController::class, 'makeWarpMove'])
            ->name('warp.move');

        Route::patch('preset/{preset}', [NavigationPresetsController::class, 'store'])
            ->name('real-space.preset.store');
    });

    Route::group(['prefix' => 'encounter'], function() {
        Route::get('current', [EncounterController::class, 'current'])
            ->name('encounter.current');

        Route::get('do/{action}', [EncounterController::class, 'doAction'])
            ->name('encounter.execute');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
