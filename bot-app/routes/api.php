<?php

use App\Http\Controllers\WebhookRouterController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook', [WebhookRouterController::class, 'handle']);
