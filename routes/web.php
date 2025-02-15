<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScraperController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/scrape', [ScraperController::class, 'scrape']);
