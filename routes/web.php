<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Profile;
use App\Http\Livewire\Users;

Route::redirect('/', 'dashboard');

/**
 * App Routes
 */
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', Profile::class)->name('profile');
});

Route::middleware(['auth','verified','can:manage-users'])->group(function() {
    Route::get('/users', Users::class)->name('users');
});


require __DIR__.'/auth.php';
