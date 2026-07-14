<?php

namespace App\Services;

class GameCommandService
{
    public function __construct(private GachaSpinService $gachaSpinService) {}

    public function process(array $messageData): bool
    {
        $prefix = config('app.game_prefix');

        $messageText = data_get($messageData, 'data.message.conversation')
            ?? data_get($messageData, 'data.message.extendedTextMessage.text')
            ?? data_get($messageData, 'data.message.imageMessage.caption')
            ?? data_get($messageData, 'data.message.videoMessage.caption')
            ?? '';

        if (!str_starts_with($messageText, $prefix)) {
            return true;
        }

        $rawCommand = trim(substr($messageText, strlen($prefix)));
        $command = strtolower(strtok($rawCommand, ' ') ?: '');

        if ($command !== 's') {
            return true;
        }

        return $this->gachaSpinService->execute($messageData);
    }
}
