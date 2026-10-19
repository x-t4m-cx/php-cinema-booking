<?php

use App\Http\Controllers\HallController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeatController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/halls')->name('home');

Route::resource('movies', MovieController::class);

Route::resource('halls', HallController::class);

/*
 * Seats are created inside a hall, but edited through their own URL so that a
 * seat can be opened directly from the seat grid.
 */
Route::prefix('halls/{hall}')->name('halls.seats.')->group(function (): void {
    Route::get('seats/create', [SeatController::class, 'create'])->name('create');
    Route::post('seats', [SeatController::class, 'store'])->name('store');
});

Route::get('seats/{seat}', [SeatController::class, 'show'])->name('seats.show');
Route::get('seats/{seat}/edit', [SeatController::class, 'edit'])->name('seats.edit');
Route::put('seats/{seat}', [SeatController::class, 'update'])->name('seats.update');
Route::delete('seats/{seat}', [SeatController::class, 'destroy'])->name('seats.destroy');
