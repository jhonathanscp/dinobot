<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MessageService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function processMessage($request)
    {
        Log::info('Recebi uma mensagem!', $request);
    }
}
