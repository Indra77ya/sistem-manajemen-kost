<?php

use App\Http\Controllers\LandingPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/branch/{branch:slug}', [LandingPageController::class, 'branchDetail'])->name('branch.detail');
Route::get('/room/{room}', [LandingPageController::class, 'roomDetail'])->name('room.detail');
