<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacebookController;

Route::get('/webhook', [FacebookController::class, 'verifyWebhook']);
Route::post('/webhook', [FacebookController::class, 'handleMessage']);

