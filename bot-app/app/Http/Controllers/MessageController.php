<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $MessageService) {}

    public function dispatch(Request $request)
    {
        if ($request->input('event') !== 'messages.upsert') {
            response()->json(['status' => 'ignored', 200]);
        }

        $remoteJid = $request->input('data.key.remoteJid');
        $fromMe = $request->input('data.key.fromMe', false);

        if (!$remoteJid || $fromMe || $remoteJid === 'status@broadcast') {
            response()->json(['status' => 'EVENT_RECEIVED', 200]);
        }

        $this->MessageService->processMessage($request->all());

        response()->json(['status' => 'EVENT_RECEIVED', 200]);
    }
}
