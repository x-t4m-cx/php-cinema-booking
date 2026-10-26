<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieSessionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/halls')->name('home');

/*
 * Public catalogue. Guests and buyers may look around: for now their only
 * action is viewing, booking and payment will be added later.
 */
Route::resource('movies', MovieController::class)
    ->only(['index', 'show'])
    ->where(['movie' => '[0-9]+']);

Route::resource('halls', HallController::class)
    ->only(['index', 'show'])
    ->where(['hall' => '[0-9]+']);

Route::get('seats/{seat}', [SeatController::class, 'show'])
    ->name('seats.show')
    ->whereNumber('seat');

/*
 * Public poster. The booking page itself is still a placeholder: choosing
 * seats and paying will be added later.
 */
Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');

Route::get('sessions/{session}/book', [BookingController::class, 'create'])
    ->name('bookings.create')
    ->whereNumber('session');

/*
 * Signing in, signing up and signing out. Every management route below
 * requires an account.
 */
Route::get('login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('login', [LoginController::class, 'store'])->name('login.store')->middleware('guest');
Route::post('logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

Route::get('register', [RegisterController::class, 'create'])->name('register')->middleware('guest');
Route::post('register', [RegisterController::class, 'store'])->name('register.store')->middleware('guest');

/*
 * Management area. The policies decide who may do what: the administrator
 * manages everything, a manager only adds films (and screenings later).
 */
Route::middleware('auth')->group(function (): void {
    Route::resource('movies', MovieController::class)->except(['index', 'show']);

    Route::resource('halls', HallController::class)->except(['index', 'show']);

    /*
     * Seats are created inside a hall, but edited through their own URL so that a
     * seat can be opened directly from the seat grid.
     */
    Route::prefix('halls/{hall}')->name('halls.seats.')->group(function (): void {
        Route::get('seats/create', [SeatController::class, 'create'])->name('create');
        Route::post('seats', [SeatController::class, 'store'])->name('store');
    });

    Route::get('seats/{seat}/edit', [SeatController::class, 'edit'])->name('seats.edit');
    Route::put('seats/{seat}', [SeatController::class, 'update'])->name('seats.update');
    Route::delete('seats/{seat}', [SeatController::class, 'destroy'])->name('seats.destroy');

    /*
     * Schedule. The occupied-slots endpoint feeds the hall timeline on the
     * session form, so it is registered before the resource.
     */
    Route::get('sessions/occupied', [MovieSessionController::class, 'occupied'])->name('sessions.occupied');
    Route::patch('sessions/{session}/cancel', [MovieSessionController::class, 'cancel'])->name('sessions.cancel');
    Route::resource('sessions', MovieSessionController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    /*
     * Account management. The policy lets only the administrator in and keeps
     * the administrator role out of every form.
     */
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'update']);
});
