<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DocsController;
use App\Http\Controllers\Api\YandexAuthController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/docs', [DocsController::class, 'index']);

Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    
    if (file_exists($fullPath)) {
        return response()->file($fullPath);
    }
    
    abort(404);
})->where('path', '.*');

// Яндекс авторизация - прямой редирект на Яндекс
Route::get('/api/auth/yandex', [YandexAuthController::class, 'redirectToYandex']);

// Яндекс callback - обрабатывает ответ от Яндекса
Route::get('/api/auth/yandex/callback', [YandexAuthController::class, 'handleYandexCallback']);