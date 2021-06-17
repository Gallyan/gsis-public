<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Profile;

Route::redirect('/', 'dashboard');

/**
 * App Routes
 */
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', Profile::class)->name('profile');
});

require __DIR__.'/auth.php';
