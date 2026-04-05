<?php

namespace App\Services;

use App\Interfaces\WhatsappProviderInterface;
use Illuminate\Support\Facades\Log;

class StickerService
{

    protected $prefix;

    public function __construct(protected WhatsappProviderInterface $whatsapp)
    {
        $this->prefix = config('app.prefix');
    }

    public function execute($message, $messageData): bool
    {
        Log::info("Mensagem: ", $message);
        Log::info("Webhook: ", $messageData);

        $jid = data_get($messageData, 'data.key.remoteJid');
        $authorName = data_get($messageData, 'data.pushName');

        $messageType = data_get($messageData, 'data.messageType');

        if ($messageType === 'imageMessage') {
            $base64Img = $this->whatsapp->getBase64Media(data_get($messageData, 'data'));

            return $this->whatsapp->sendSticker($jid, $authorName, $base64Img);
        }

        return true;
    }
}
