<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessGameCommandJob;
use Illuminate\Http\Request;

class GameWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        if ($request->input('type') !== 'Message') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $messageText = $request->input('data.message.conversation')
            ?? $request->input('data.message.extendedTextMessage.text')
            ?? $request->input('data.message.imageMessage.caption')
            ?? $request->input('data.message.videoMessage.caption')
            ?? '';

        if (!str_starts_with($messageText, config('app.game_prefix'))) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $remoteJid = (string) $request->input('data.info.remoteJid', '');
        $fromMe = (bool) $request->input('data.info.fromMe', false);
        $allowedGroupJid = (string) config('app.game_allowed_group_jid', '');

        if (
            $fromMe
            || $remoteJid === ''
            || $remoteJid === 'status@broadcast'
            || !str_ends_with($remoteJid, '@g.us')
            || $allowedGroupJid === ''
            || $remoteJid !== $allowedGroupJid
        ) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $jobPayload = [
            'data' => [
                'info' => [
                    'remoteJid' => $remoteJid,
                    'fromMe' => $fromMe,
                ],
                'message' => [
                    'conversation' => $messageText,
                ],
            ],
        ];

        ProcessGameCommandJob::dispatch($jobPayload);

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }
}
