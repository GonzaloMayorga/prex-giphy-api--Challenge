<?php

declare(strict_types=1);

use App\Infrastructure\Http\Controllers\Gif\GetGifByIdController;
use App\Infrastructure\Http\Controllers\Gif\SearchGifsController;
use Illuminate\Support\Facades\Route;

Route::get('/gifs', SearchGifsController::class)
    ->name('gifs.search');

Route::get('/gifs/{id}', GetGifByIdController::class)
    ->name('api.gifs.get');

?>