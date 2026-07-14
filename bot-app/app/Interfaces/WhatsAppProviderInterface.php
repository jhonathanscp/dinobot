<?php

namespace App\Interfaces;

interface WhatsAppProviderInterface
{
    public function sendText($to, $message): bool;

    public function sendSticker($to, $creatorName, $img): bool;

    public function sendImage($to, $imageUrl, $caption): bool;

    public function getBase64Media($messageBody): ?string;
}
