<?php

namespace App\Services;

use App\Models\Character;
use App\Models\CharacterIdPool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CharacterCatalogService
{
    public function resolveFromPool(CharacterIdPool $poolCharacter): ?Character
    {
        $character = Character::query()
            ->where('provider', $poolCharacter->provider)
            ->where('provider_character_id', $poolCharacter->provider_character_id)
            ->first();

        if ($character) {
            return $character;
        }

        $normalized = match (strtolower($poolCharacter->provider)) {
            'anilist' => $this->fetchFromAniList($poolCharacter->provider_character_id),
            'tmdb' => $this->fetchFromTmdb($poolCharacter->provider_character_id),
            'igdb' => $this->fetchFromIgdb($poolCharacter->provider_character_id),
            'superhero' => $this->fetchFromSuperHero($poolCharacter->provider_character_id),
            default => null,
        };

        if (!$normalized || empty($normalized['name']) || empty($normalized['image_url'])) {
            return null;
        }

        return Character::query()->create([
            'provider' => $poolCharacter->provider,
            'provider_character_id' => $poolCharacter->provider_character_id,
            'name' => $normalized['name'],
            'work' => $normalized['work'] ?? null,
            'image_url' => $normalized['image_url'],
            'popularity' => $normalized['popularity'] ?? null,
            'raw_payload' => $normalized['raw_payload'] ?? null,
        ]);
    }

    private function fetchFromAniList(string $characterId): ?array
    {
        $apiUrl = (string) config('services.anilist.url');

        $response = Http::post($apiUrl, [
            'query' => 'query ($id: Int) { Character(id: $id) { id name { full } image { large } favourites media(perPage: 1) { edges { node { title { romaji english native } } } } } }',
            'variables' => ['id' => (int) $characterId],
        ]);

        if ($response->failed()) {
            Log::error('Falha ao buscar personagem na AniList.', ['character_id' => $characterId]);
            return null;
        }

        $payload = $response->json();
        $character = data_get($payload, 'data.Character');

        if (!$character) {
            return null;
        }

        $work = data_get($character, 'media.edges.0.node.title.romaji')
            ?? data_get($character, 'media.edges.0.node.title.english')
            ?? data_get($character, 'media.edges.0.node.title.native');

        return [
            'name' => data_get($character, 'name.full'),
            'work' => $work,
            'image_url' => data_get($character, 'image.large'),
            'popularity' => data_get($character, 'favourites'),
            'raw_payload' => $payload,
        ];
    }

    private function fetchFromTmdb(string $characterId): ?array
    {
        $apiKey = (string) config('services.tmdb.api_key');
        $apiUrl = rtrim((string) config('services.tmdb.url'), '/');

        if ($apiKey === '') {
            Log::warning('TMDb API key não configurada.');
            return null;
        }

        $response = Http::get(sprintf('%s/person/%s', $apiUrl, $characterId), [
            'api_key' => $apiKey,
            'language' => 'pt-BR',
        ]);

        if ($response->failed()) {
            Log::error('Falha ao buscar personagem no TMDb.', ['character_id' => $characterId]);
            return null;
        }

        $payload = $response->json();
        $profilePath = data_get($payload, 'profile_path');

        if (!$profilePath) {
            return null;
        }

        $imageUrl = rtrim((string) config('services.tmdb.image_base_url'), '/') . $profilePath;

        return [
            'name' => data_get($payload, 'name'),
            'work' => data_get($payload, 'known_for_department'),
            'image_url' => $imageUrl,
            'popularity' => (int) data_get($payload, 'popularity'),
            'raw_payload' => $payload,
        ];
    }

    private function fetchFromIgdb(string $characterId): ?array
    {
        $clientId = (string) config('services.igdb.client_id');
        $accessToken = (string) config('services.igdb.access_token');
        $apiUrl = rtrim((string) config('services.igdb.url'), '/');

        if ($clientId === '' || $accessToken === '') {
            Log::warning('Credenciais IGDB não configuradas.');
            return null;
        }

        $response = Http::withHeaders([
            'Client-ID' => $clientId,
            'Authorization' => 'Bearer ' . $accessToken,
        ])->withBody(sprintf('fields name,image_id; where id = %s; limit 1;', (int) $characterId), 'text/plain')
            ->post($apiUrl . '/characters');

        if ($response->failed()) {
            Log::error('Falha ao buscar personagem na IGDB.', ['character_id' => $characterId]);
            return null;
        }

        $payload = $response->json();
        $character = $payload[0] ?? null;

        if (!$character || empty($character['image_id'])) {
            return null;
        }

        $imageUrl = sprintf(
            '%s/%s.jpg',
            rtrim((string) config('services.igdb.image_base_url'), '/'),
            $character['image_id']
        );

        return [
            'name' => data_get($character, 'name'),
            'work' => 'IGDB',
            'image_url' => $imageUrl,
            'popularity' => null,
            'raw_payload' => $payload,
        ];
    }

    private function fetchFromSuperHero(string $characterId): ?array
    {
        $token = (string) config('services.superhero.token');
        $apiUrl = rtrim((string) config('services.superhero.url'), '/');

        if ($token === '') {
            Log::warning('Token da SuperHero API não configurado.');
            return null;
        }

        $response = Http::get($apiUrl . '/' . $token . '/' . $characterId);

        if ($response->failed()) {
            Log::error('Falha ao buscar personagem na SuperHero API.', ['character_id' => $characterId]);
            return null;
        }

        $payload = $response->json();

        if (data_get($payload, 'response') !== 'success') {
            return null;
        }

        return [
            'name' => data_get($payload, 'name'),
            'work' => data_get($payload, 'biography.publisher'),
            'image_url' => data_get($payload, 'image.url'),
            'popularity' => (int) data_get($payload, 'powerstats.intelligence'),
            'raw_payload' => $payload,
        ];
    }
}
