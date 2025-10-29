<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Event;
use App\Livewire\Guest;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['api.auth'])
    ->name('dashboard');

Route::middleware(['api.auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');


    Route::prefix('events')->group(function () {
        Route::get('/', Event::class)->name('events.index');
        Volt::route('/show/{name}', 'events.eventShow')->name('events.show');
        Volt::route('/add', 'events.eventCreate')->name('events.create');
        Volt::route('/edit/{name}', 'events.eventEdit')->name('events.edit');
    });

    Route::prefix('guest')->group(function () {
        Route::get('/', Guest::class)->name('guest.index');
        Volt::route('/confirm/{code}', 'guestsConfirm')->name('guests.confirm');
    });
});
Volt::route('scan/admin/{slug}', 'scan.admin')->name('scan.admin');

require __DIR__.'/auth.php';
