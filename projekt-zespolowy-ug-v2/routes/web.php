<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/webhook', [FacebookController::class, 'verifyWebhook']);
Route::post('/webhook', [FacebookController::class, 'handleMessage']);
