<?php

declare(strict_types=1);

function env_value(string $key, ?string $default = null): ?string
{
    static $env = null;

    if ($env === null) {
        $env = [];
        $envFile = BASE_PATH . '/.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$k, $v] = explode('=', $line, 2);
                $env[trim($k)] = trim($v);
            }
        }
    }

    return $env[$key] ?? $default;
}

define('APP_NAME', env_value('APP_NAME', 'Auditor App'));
define('APP_URL', env_value('APP_URL', 'http://localhost/auditor-app/public'));
