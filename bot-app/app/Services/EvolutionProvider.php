<?php

namespace App\Services;

use App\Interfaces\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionProvider implements WhatsAppProviderInterface
{
    protected $apiUrl;
    protected $apiKey;
    protected $instanceName;

    public function __construct()
    {
        $this->apiUrl = config('app.evolution_api_url');
        $this->apiKey = config('app.evolution_api_key');
        $this->instanceName = config('app.instance_name');
    }

    public function sendText($to, $message): bool
    {
        try {
            Log::info("Enviando mensagem via Evolution:", [
                'url' => $this->apiUrl,
                'to'  => $to
            ]);

            $response = Http::withHeaders([
                'apikey' => $this->apiKey
            ])->post("{$this->apiUrl}/message/sendText/{$this->instanceName}", [
                'number'      => $to,
                'text'        => $message,
                'delay'       => 1200,
                'linkPreview' => true
            ]);

            if ($response->failed()) {
                Log::error("Falha ao enviar mensagem Evolution:", $response->json() ?? []);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Erro no EvolutionProvider: " . $e->getMessage());
            return false;
        }
    }

    public function sendSticker($to, $authorName, $imgBase64): bool
    {
        Log::info("Enviando figurinha via Evolution:", [
            'url' => $this->apiUrl,
            'to'  => $to
        ]);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey
            ])->post("{$this->apiUrl}/message/sendSticker/{$this->instanceName}", [
                'number'  => $to,
                'sticker' => $imgBase64,
                'delay'       => 1200

            ]);

            if ($response->failed()) {
                Log::error('Erro no ao enviar figurinha: ', $response->json() ?? []);

                $this->sendText($to, "Dino ainda não consegue converter esse tipo de mídia 😥");

                return false;
            }

            return true;
        } catch (\Exception $exception) {
            Log::error('Erro no evolution provider: ', $exception->getMessage());

            return false;
        }
    }

    public function getBase64Media($messageBody): ?string
    {
        Log::info("Solicitando imagem em Base64 à EvolutionAPI:", [
            'url' => $this->apiUrl,
        ]);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey
            ])->post("{$this->apiUrl}/chat/getBase64FromMediaMessage/{$this->instanceName}", [
                'message' => $messageBody,
            ]);

            if ($response->failed()) {
                Log::error('Erro no ao obter imagem: ', $response->json() ?? []);

                return false;
            }

            $base64 = $response->json('base64');

            return $base64;
        } catch (\Exception $exception) {
            Log::error('Erro no evolution provider: ', $exception->getMessage());

            return false;
        }
    }
}
