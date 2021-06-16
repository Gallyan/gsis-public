<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');

Route::get('/dashboard', function () {
    return view('dashboard', ['user'=>auth()->user()] );
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
