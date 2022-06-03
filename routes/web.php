<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Users;
use App\Http\Livewire\Institutions;
use App\Http\Livewire\Orders;
use App\Http\Livewire\EditOrder;
use App\Http\Livewire\Purchases;
use App\Http\Livewire\EditPurchase;
use App\Http\Livewire\EditUser;

/**
 * App Routes
 */
Route::middleware(['auth','throttle:global'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/user/{id}', EditUser::class)->whereNumber('id')->name('edit-user');
});

Route::middleware(['auth','verified','throttle:global'])->group(function () {
    Route::get('/orders', Orders::class)->name('orders');
    Route::get('/order/{id?}', EditOrder::class)->whereNumber('id')->name('edit-order');
    Route::get('/purchases', Purchases::class)->name('purchases');
    Route::get('/purchase/{id?}', EditPurchase::class)->whereNumber('id')->name('edit-purchase');
    Route::get('/documents/{id}', 'App\Http\Controllers\DocumentController@download')->whereNumber('id')->name('download');

    // Admin
    Route::middleware(['can:manage-users'])->group(function() {
        Route::get('/users', Users::class)->name('users');
        Route::get('/institutions', Institutions::class)->name('institutions');
    });
});

require __DIR__.'/auth.php';
