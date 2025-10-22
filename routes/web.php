<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Event;
use App\Livewire\Guest;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Route::get('events', Event::class)->name('events.show');
    Route::get('guest', Guest::class)->name('guest.show');
    Volt::route('scan/admin', 'scan.admin')->name('scan.admin');
    Volt::route('guests/confirm/{code}', 'guestsConfirm')->name('guests.confirm');
});

require __DIR__.'/auth.php';
