<?php

namespace App\Services;

use App\Interfaces\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Log;

class HelpService
{

    protected $prefix;
    protected $helpMessage;
    protected $notFoundMessage;

    public function __construct(protected WhatsAppProviderInterface $whatsapp)
    {
        $this->prefix = config('app.prefix');

        $this->helpMessage = "🦖 *DinoBot - Menu de Comandos*\n\n"
            . "*{$this->prefix}help*: Exibe esta lista de comandos.\n\n"
            . "*{$this->prefix}fig*: Transforma imagem/vídeo em figurinha. (Envie com a mídia ou responda a uma).";

        $this->notFoundMessage = "❌ *Ops! Esse comando não existe.*\n\n"
            . "Para consultar a lista de comandos, digite *{$this->prefix}help* e veja o que eu posso fazer por você! 🦖";
    }


    public function execute($message, $messageData): bool
    {
        $jid = data_get($messageData, 'data.info.remoteJid');
        error_log("HELPSERVICE EXECUTE CALLED! JID: $jid");

        if (!$jid) {
            error_log("HelpService: JID não encontrado no payload.");
            Log::error("HelpService: JID não encontrado no payload.");
            return false;
        }

        if ($message[1] !== 'help') {
            error_log("HELPSERVICE: SENDING NOT FOUND MESSAGE");
            return $this->whatsapp->sendText($jid, $this->notFoundMessage);
        }

        error_log("HELPSERVICE: SENDING HELP MESSAGE");
        return $this->whatsapp->sendText($jid, $this->helpMessage);
    }
}
