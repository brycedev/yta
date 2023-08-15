<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\SyncController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
});

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/channels/verify', [ChannelController::class, 'verify'])->name('channel.verify');
    Route::post('/channels', [ChannelController::class, 'store'])->name('channel.store');
    Route::put('/channels', [ChannelController::class, 'update'])->name('channel.update');
    Route::get('/channels/fetch', [ChannelController::class, 'fetch'])->name('channel.fetch');
    Route::post('/channels/refresh', [ChannelController::class, 'refresh'])->name('channel.refresh');
    Route::post('/syncs', [SyncController::class, 'store'])->name('sync.store');
    Route::post('/syncs/all', [SyncController::class, 'all'])->name('sync.all');
    Route::delete('/channels', [ChannelController::class, 'destroy'])->name('channel.destroy');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
