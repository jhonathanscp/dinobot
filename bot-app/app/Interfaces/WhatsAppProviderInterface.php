<?php

namespace App\Interfaces;

interface WhatsappProviderInterface
{
    public function sendText($to, $message): bool;

    public function sendSticker($to, $creatorName, $img): bool;

    public function getBase64Media($messageBody): ?string;
}
