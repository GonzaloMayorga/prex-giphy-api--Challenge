<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\Auth\LoginController;
use App\Infrastructure\Http\Controllers\Gif\GetGifByIdController;
use App\Infrastructure\Http\Controllers\Gif\SearchGifsController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)
    ->middleware('throttle:10,1')
    ->name('api.auth.login');


Route::middleware('auth:api')
->group(function (): void {
    Route::get('/gifs', SearchGifsController::class)
    ->name('api.gifs.search');
    
    Route::get('/gifs/{id}', GetGifByIdController::class)
    ->name('api.gifs.get');
    })

?>