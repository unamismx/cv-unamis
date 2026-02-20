<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TranslationService
{
    public function isEnabled(): bool
    {
        return trim((string) config('services.google_translate.api_key')) !== '';
    }

    /**
     * @param array<string, string> $map
     * @return array<string, string>
     */
    public function translateMap(array $map, string $source = 'es', string $target = 'en'): array
    {
        $apiKey = trim((string) config('services.google_translate.api_key'));
        if ($apiKey === '') {
            return $map;
        }

        $indexToKey = [];
        $texts = [];
        foreach ($map as $key => $value) {
            $clean = trim((string) $value);
            if ($clean === '') {
                continue;
            }
            $indexToKey[] = $key;
            $texts[] = $clean;
        }

        if ($texts === []) {
            return $map;
        }

        $response = Http::timeout(15)->post(
            'https://translation.googleapis.com/language/translate/v2',
            [
                'key' => $apiKey,
                'q' => $texts,
                'source' => $source,
                'target' => $target,
                'format' => 'text',
            ]
        );

        if (! $response->ok()) {
            return $map;
        }

        $translations = $response->json('data.translations');
        if (! is_array($translations)) {
            return $map;
        }

        $result = $map;
        foreach ($translations as $idx => $row) {
            $path = $indexToKey[$idx] ?? null;
            if (! $path || ! is_array($row)) {
                continue;
            }
            $translated = html_entity_decode((string) ($row['translatedText'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($translated !== '') {
                $result[$path] = trim($translated);
            }
        }

        return $result;
    }
}

