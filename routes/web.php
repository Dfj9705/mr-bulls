<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'shop.home')
    ->name('home');