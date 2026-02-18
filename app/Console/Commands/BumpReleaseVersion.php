<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BumpReleaseVersion extends Command
{
    protected $signature = 'release:bump';
    protected $description = 'Incrementa versión interna D.C y actualiza fecha/hora de última actualización.';

    public function handle(): int
    {
        $envPath = base_path('.env');
        if (! is_file($envPath)) {
            $this->error('No se encontró el archivo .env.');
            return self::FAILURE;
        }

        $timezone = (string) config('app.timezone', 'America/Merida');
        $now = Carbon::now($timezone);
        $today = $now->toDateString();
        $dateTime = $now->format('Y-m-d H:i:s');

        $rawVersion = (string) env('APP_VERSION', '1.0');
        preg_match('/^(\d+)\.(\d+)/', $rawVersion, $matches);
        $fallbackDay = isset($matches[1]) ? (int) $matches[1] : 1;
        $fallbackDaily = isset($matches[2]) ? (int) $matches[2] : 0;

        $dayIndex = (int) env('APP_RELEASE_DAY_INDEX', $fallbackDay);
        $dailyCount = (int) env('APP_RELEASE_DAILY_COUNT', $fallbackDaily);
        $lastDate = (string) env('APP_RELEASE_LAST_DATE', $today);

        if ($lastDate === $today) {
            $dailyCount++;
        } else {
            $dayIndex++;
            $dailyCount = 1;
            $lastDate = $today;
        }

        $version = $dayIndex . '.' . $dailyCount;

        $contents = file_get_contents($envPath);
        if (! is_string($contents)) {
            $this->error('No se pudo leer el archivo .env.');
            return self::FAILURE;
        }

        $contents = $this->upsertEnv($contents, 'APP_RELEASE_DAY_INDEX', (string) $dayIndex);
        $contents = $this->upsertEnv($contents, 'APP_RELEASE_DAILY_COUNT', (string) $dailyCount);
        $contents = $this->upsertEnv($contents, 'APP_RELEASE_LAST_DATE', $lastDate);
        $contents = $this->upsertEnv($contents, 'APP_VERSION', $version);
        $contents = $this->upsertEnv($contents, 'APP_LAST_UPDATE', '"' . $dateTime . '"');

        file_put_contents($envPath, $contents);

        $this->info('Versión actualizada: ' . $version);
        $this->info('Última actualización: ' . $dateTime . ' (' . $timezone . ')');
        $this->comment('Ejecuta: php artisan optimize:clear');

        return self::SUCCESS;
    }

    private function upsertEnv(string $contents, string $key, string $value): string
    {
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        $line = $key . '=' . $value;

        if (preg_match($pattern, $contents)) {
            return (string) preg_replace($pattern, $line, $contents);
        }

        return rtrim($contents) . PHP_EOL . $line . PHP_EOL;
    }
}
