<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private MessageService $MessageService) {}

    public function handleWebhook(Request $request)
    {
        error_log("MESSAGE CONTROLLER CALLED!");
        if ($request->input('type') !== 'Message' && !$request->has('event.Message')) {
            // Note: with the new Wuzapi mapping, it might just bypass this if we trust the router
        }

        $messageText = $request->input('data.message.conversation')
            ?? $request->input('data.message.extendedTextMessage.text')
            ?? $request->input('data.message.imageMessage.caption')
            ?? '';

        if (!str_starts_with($messageText, config('app.prefix'))) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $remoteJid = $request->input('data.info.remoteJid');
        $fromMe = $request->input('data.info.fromMe', false);

        error_log("MESSAGECONTROLLER JID: '$remoteJid', fromMe: " . ($fromMe ? 'true' : 'false'));

        if (!$remoteJid || $fromMe || $remoteJid === 'status@broadcast') {
            error_log("MESSAGECONTROLLER ABORTING EARLY! (No JID, fromMe, or broadcast)");
            return response()->json(['status' => 'EVENT_RECEIVED'], 200);
        }

        error_log("CALLING MessageService processMessage");
        $this->MessageService->processMessage($request->all());

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }
}
