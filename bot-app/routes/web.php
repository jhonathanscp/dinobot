<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        "DinoBot" => "online",
        "version" => env('APP_VERSION', 'production')
    ]);
});
