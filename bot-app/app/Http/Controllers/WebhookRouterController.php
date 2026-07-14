<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookRouterController extends Controller
{
    public function __construct(
        private MessageController $messageController,
        private GameWebhookController $gameWebhookController,
    ) {}

    public function handle(Request $request)
    {
        if ($request->input('event') !== 'messages.upsert') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $messageText = $request->input('data.message.conversation')
            ?? $request->input('data.message.extendedTextMessage.text')
            ?? $request->input('data.message.imageMessage.caption')
            ?? $request->input('data.message.videoMessage.caption')
            ?? '';

        if (str_starts_with($messageText, config('app.game_prefix'))) {
            return $this->gameWebhookController->handleWebhook($request);
        }

        if (str_starts_with($messageText, config('app.prefix'))) {
            return $this->messageController->handleWebhook($request);
        }

        return response()->json(['status' => 'ignored'], 200);
    }
}
