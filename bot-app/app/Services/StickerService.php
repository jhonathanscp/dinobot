<?php

namespace App\Services;

use App\Interfaces\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Log;

class StickerService
{

    protected $prefix;

    public function __construct(protected WhatsAppProviderInterface $whatsapp)
    {
        $this->prefix = config('app.prefix');
    }

    public function execute($message, $messageData): bool
    {
        Log::info("Mensagem: ", $message);
        Log::info("Webhook: ", [data_get($messageData, 'data.message.extendedTextMessage.contextInfo.stanzaId')]);

        $jid = data_get($messageData, 'data.info.remoteJid');
        $authorName = data_get($messageData, 'data.info.pushName');

        $messageKeys = array_keys(data_get($messageData, 'data.message', []));
        $messageType = collect($messageKeys)->first(fn ($key) => $key !== 'messageContextInfo');

        if ($messageType === 'imageMessage') {
            $base64Img = $this->whatsapp->getBase64Media(data_get($messageData, 'data'));

            return $this->whatsapp->sendSticker($jid, $authorName, $base64Img);
        }

        if ($messageType === 'conversation' || $messageType === 'extendedTextMessage') {
            $stanzaId = data_get($messageData, 'data.message.extendedTextMessage.contextInfo.stanzaId');

            if ($stanzaId === null) {
                return $this->whatsapp->sendText($jid, "⁉️ Para criar uma figurinha, você precisa enviar uma imagem com o comando ou responder a uma imagem existente.");
            }

            $quotedMessage = data_get($messageData, 'data.message.extendedTextMessage.contextInfo.quotedMessage', []);

            $base64Img = $this->whatsapp->getBase64Media([
                'message' => $quotedMessage,
            ]);

            return $this->whatsapp->sendSticker($jid, $authorName, $base64Img);
        }

        return true;
    }
}
