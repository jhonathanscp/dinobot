<?php

namespace App\Services;

use App\Interfaces\WhatsAppProviderInterface;
use App\Models\CharacterIdPool;

class GachaSpinService
{
    public function __construct(
        private CharacterCatalogService $characterCatalogService,
        private WhatsAppProviderInterface $whatsapp,
    ) {}

    public function execute(array $messageData): bool
    {
        $remoteJid = data_get($messageData, 'data.info.remoteJid');

        if (!$remoteJid) {
            return false;
        }

        $poolCharacter = CharacterIdPool::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (!$poolCharacter) {
            return $this->whatsapp->sendText($remoteJid, '⚠️ O pool de personagens ainda está vazio.');
        }

        $character = $this->characterCatalogService->resolveFromPool($poolCharacter);

        if (!$character) {
            return $this->whatsapp->sendText($remoteJid, '⚠️ Não consegui carregar essa carta agora. Tente novamente.');
        }

        $caption = sprintf(
            "🎴 *%s*\n🧩 Obra: %s\n📈 Popularidade: %s\n🏷️ Fonte: %s",
            $character->name,
            $character->work ?? 'Desconhecida',
            $character->popularity ?? 'N/A',
            strtoupper($character->provider)
        );

        return $this->whatsapp->sendImage($remoteJid, $character->image_url, $caption);
    }
}
