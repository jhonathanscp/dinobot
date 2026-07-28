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
        // A Wuzapi empacota o JSON real como uma string dentro da chave "jsonData"
        $rawJsonData = $request->input('jsonData');
        if (!$rawJsonData) {
            error_log("WEBHOOK IGNORADO: Sem jsonData");
            return response()->json(['status' => 'ignored'], 200);
        }

        $wuzapiData = json_decode($rawJsonData, true) ?? [];
        $eventType = $wuzapiData['type'] ?? $wuzapiData['event'] ?? '';

        if ($eventType !== 'Message' && !isset($wuzapiData['event']['Message'])) {
            error_log("WEBHOOK IGNORADO: Não é um evento de Mensagem");
            return response()->json(['status' => 'ignored'], 200);
        }

        error_log("WEBHOOK RECEBIDO! Evento de Mensagem detectado.");

        $wuzapiMessage = $wuzapiData['event']['Message'] ?? [];
        $wuzapiInfo = $wuzapiData['event']['Info'] ?? [];

        $isGroup = $wuzapiInfo['IsGroup'] ?? false;
        
        if ($isGroup) {
            $remoteJid = $wuzapiInfo['Chat'];
        } else {
            // Em chats privados, Wuzapi às vezes coloca @lid no Chat e o número real no SenderAlt
            $remoteJid = !empty($wuzapiInfo['SenderAlt']) ? $wuzapiInfo['SenderAlt'] : ($wuzapiInfo['Chat'] ?? '');
        }

        // Convert to Evolution API format expected by the bot
        $simulatedEvolutionPayload = [
            'data' => [
                'message' => $wuzapiMessage,
                'info' => [
                    'remoteJid' => $remoteJid,
                    'fromMe' => $wuzapiInfo['IsFromMe'] ?? false,
                    'id' => $wuzapiInfo['ID'] ?? '',
                ]
            ]
        ];

        // Replace request data with the simulated payload
        $request->merge($simulatedEvolutionPayload);

        $messageText = $request->input('data.message.conversation')
            ?? $request->input('data.message.extendedTextMessage.text')
            ?? $request->input('data.message.imageMessage.caption')
            ?? $request->input('data.message.videoMessage.caption')
            ?? '';

        error_log("EXTRACTED MESSAGE TEXT: '" . $messageText . "'");

        if (str_starts_with($messageText, config('app.game_prefix'))) {
            return $this->gameWebhookController->handleWebhook($request);
        }

        if (str_starts_with($messageText, config('app.prefix'))) {
            return $this->messageController->handleWebhook($request);
        }

        return response()->json(['status' => 'ignored'], 200);
    }
}
