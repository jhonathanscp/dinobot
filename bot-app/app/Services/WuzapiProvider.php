<?php

namespace App\Services;

use App\Interfaces\WhatsAppProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WuzapiProvider implements WhatsAppProviderInterface
{
    protected string $apiUrl;
    protected string $token;

    public function __construct()
    {
        $this->apiUrl = rtrim((string) config('app.wuzapi_url'), '/');
        $this->token = (string) config('app.wuzapi_token');
    }

    public function sendText($to, $message): bool
    {
        try {
            error_log("WUZAPI SENDTEXT CALLED! To: $to, Msg: $message");
            Log::info('Enviando mensagem via Wuzapi:', [
                'url' => $this->apiUrl,
                'to'  => $to,
            ]);

            $response = Http::withHeaders([
                'Token' => $this->token,
            ])->post("{$this->apiUrl}/chat/send/text", [
                'Phone' => $this->formatPhone($to),
                'Body'  => $message,
            ]);

            error_log("WUZAPI RESPONSE STATUS: " . $response->status());
            error_log("WUZAPI RESPONSE BODY: " . $response->body());

            if ($response->failed()) {
                Log::error('Falha ao enviar mensagem via Wuzapi:', $response->json() ?? []);
                error_log("WUZAPI SENDTEXT FAILED!");
                return false;
            }

            error_log("WUZAPI SENDTEXT SUCCESS!");
            return true;
        } catch (\Exception $e) {
            error_log("WUZAPI EXCEPTION: " . $e->getMessage());
            Log::error('Erro no WuzapiProvider::sendText: ' . $e->getMessage());
            return false;
        }
    }

    public function sendSticker($to, $authorName, $imgBase64): bool
    {
        Log::info('Enviando figurinha via Wuzapi:', [
            'url' => $this->apiUrl,
            'to'  => $to,
        ]);

        try {
            $stickerData = $imgBase64;
            if (!str_starts_with($stickerData, 'data:')) {
                $stickerData = 'data:image/webp;base64,' . $stickerData;
            }

            $response = Http::withHeaders([
                'Token' => $this->token,
            ])->post("{$this->apiUrl}/chat/send/sticker", [
                'Phone'   => $this->formatPhone($to),
                'Sticker' => $stickerData,
            ]);

            if ($response->failed()) {
                Log::error('Erro ao enviar figurinha via Wuzapi:', $response->json() ?? []);

                $this->sendText($to, "Dino ainda não consegue converter esse tipo de mídia \xF0\x9F\x98\xA5");

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro no WuzapiProvider::sendSticker: ' . $e->getMessage());
            return false;
        }
    }

    public function sendImage($to, $imageUrl, $caption): bool
    {
        Log::info('Enviando imagem via Wuzapi:', [
            'url' => $this->apiUrl,
            'to'  => $to,
        ]);

        try {
            $imageData = $imageUrl;
            if (!str_starts_with($imageData, 'data:')) {
                $imageData = 'data:image/jpeg;base64,' . base64_encode(
                    Http::get($imageUrl)->body()
                );
            }

            $response = Http::withHeaders([
                'Token' => $this->token,
            ])->post("{$this->apiUrl}/chat/send/image", [
                'Phone'   => $this->formatPhone($to),
                'Image'   => $imageData,
                'Caption' => $caption,
            ]);

            if ($response->failed()) {
                Log::error('Erro ao enviar imagem via Wuzapi:', $response->json() ?? []);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro no WuzapiProvider::sendImage: ' . $e->getMessage());
            return false;
        }
    }

    public function getBase64Media($messageBody): ?string
    {
        Log::info('Solicitando mídia em Base64 à Wuzapi:', [
            'url' => $this->apiUrl,
        ]);

        try {
            $response = Http::withHeaders([
                'Token' => $this->token,
            ])->post("{$this->apiUrl}/chat/downloadimage", [
                'Url'        => data_get($messageBody, 'message.imageMessage.url', ''),
                'MediaKey'   => data_get($messageBody, 'message.imageMessage.mediaKey', ''),
                'Mimetype'   => data_get($messageBody, 'message.imageMessage.mimetype', 'image/jpeg'),
                'FileSHA256' => data_get($messageBody, 'message.imageMessage.fileSha256', ''),
                'FileLength' => (int) data_get($messageBody, 'message.imageMessage.fileLength', 0),
            ]);

            if ($response->failed()) {
                Log::error('Erro ao obter mídia via Wuzapi:', $response->json() ?? []);
                return null;
            }

            return $response->json('data.base64') ?? $response->json('data.Base64') ?? $response->body();
        } catch (\Exception $e) {
            Log::error('Erro no WuzapiProvider::getBase64Media: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Formata o número de telefone para o padrão Wuzapi.
     * Remove o sufixo @s.whatsapp.net ou @g.us se presente,
     * pois a Wuzapi aceita apenas o número puro.
     */
    protected function formatPhone(string $jid): string
    {
        return preg_replace('/@.*$/', '', $jid);
    }
}
