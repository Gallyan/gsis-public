<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\EditExpense;
use App\Http\Livewire\EditMission;
use App\Http\Livewire\EditOrder;
use App\Http\Livewire\EditPurchase;
use App\Http\Livewire\EditUser;
use App\Http\Livewire\Institutions;
use App\Http\Livewire\Expenses;
use App\Http\Livewire\Missions;
use App\Http\Livewire\Orders;
use App\Http\Livewire\Purchases;
use App\Http\Livewire\Users;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

/**
 * App Routes
 */
Route::middleware(['auth', 'throttle:global'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/user/{id}', EditUser::class)->whereNumber('id')->name('edit-user');
});

Route::middleware(['auth', 'verified', 'throttle:global'])->group(function () {
    Route::get('/orders', Orders::class)->name('orders');
    Route::get('/order/{id?}', EditOrder::class)->whereNumber('id')->name('edit-order');
    Route::get('/order/{id?}/zip', [DocumentController::class, 'order'])->whereNumber('id')->name('zip-order');

    Route::get('/purchases', Purchases::class)->name('purchases');
    Route::get('/purchase/{id?}', EditPurchase::class)->whereNumber('id')->name('edit-purchase');
    Route::get('/purchase/{id?}/zip', [DocumentController::class, 'purchase'])->whereNumber('id')->name('zip-purchase');

    Route::get('/missions', Missions::class)->name('missions');
    Route::get('/mission/{id?}', EditMission::class)->whereNumber('id')->name('edit-mission');
    Route::get('/mission/{id?}/zip', [DocumentController::class, 'mission'])->whereNumber('id')->name('zip-mission');

    Route::get('/expenses', Expenses::class)->name('expenses');
    Route::get('/mission/{mission}/expense/{expense?}', EditExpense::class)->whereNumber('mission')->whereNumber('expense')->name('edit-expense');
    Route::get('/mission/{mission}/expense/{expense?}/zip', [DocumentController::class, 'expense'])->whereNumber('mission')->whereNumber('expense')->name('zip-expense');

    Route::get('/documents/{id}', [DocumentController::class, 'download'])->whereNumber('id')->name('download');

    Route::get('/post/{id?}/zip', [DocumentController::class, 'post'])->whereNumber('id')->name('zip-post');

    // Admin
    Route::middleware(['can:manage-users'])->group(function () {
        Route::get('/users', Users::class)->name('users');
        Route::get('/institutions', Institutions::class)->name('institutions');
    });
});

require __DIR__.'/auth.php';
