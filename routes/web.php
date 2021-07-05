<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Profile;
use App\Http\Livewire\Users;
use App\Http\Livewire\Institutions;
use App\Http\Livewire\Orders;

Route::redirect('/', 'dashboard');

/**
 * App Routes
 */
Route::middleware(['auth','verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', Profile::class)->name('profile');
    Route::get('/orders', Orders::class)->name('orders');

    // Admin
    Route::middleware(['can:manage-users'])->group(function() {
        Route::get('/users', Users::class)->name('users');
        Route::get('/institutions', Institutions::class)->name('institutions');
    });
});


require __DIR__.'/auth.php';
