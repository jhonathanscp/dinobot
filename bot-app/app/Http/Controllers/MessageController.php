<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $MessageService) {}

    public function handleWebhook(Request $request)
    {
        if ($request->input('event') !== 'messages.upsert') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $messageText = $request->input('data.message.conversation')
            ?? $request->input('data.message.extendedTextMessage.text')
            ?? $request->input('data.message.imageMessage.caption')
            ?? '';

        if (!str_starts_with($messageText, config('app.prefix'))) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $remoteJid = $request->input('data.key.remoteJid');
        $fromMe = $request->input('data.key.fromMe', false);

        if (!$remoteJid || $fromMe || $remoteJid === 'status@broadcast') {
            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        }

        $this->MessageService->processMessage($request->all());

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }
}
