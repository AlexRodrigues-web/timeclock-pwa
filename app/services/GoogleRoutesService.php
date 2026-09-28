<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class GoogleRoutesService
{
    private const ENDPOINT =
        'https://routes.googleapis.com/directions/v2:computeRoutes';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = self::readApiKey();
    }

    public function calculateRoundTrip(
        string $originAddress,
        array $stopAddresses
    ): array {
        $originAddress = trim($originAddress);

        $stopAddresses = array_values(
            array_filter(
                array_map(
                    static fn ($value): string =>
                        trim((string) $value),
                    $stopAddresses
                ),
                static fn (string $value): bool =>
                    $value !== ''
            )
        );

        if ($originAddress === '') {
            throw new RuntimeException(
                'Informe o ponto de saída.'
            );
        }

        if (empty($stopAddresses)) {
            throw new RuntimeException(
                'Nenhuma loja foi encontrada nas marcações de hoje.'
            );
        }

        if ($this->apiKey === '') {
            throw new RuntimeException(
                'Google Maps ainda não está configurado. ' .
                'Adicione GOOGLE_MAPS_API_KEY no arquivo .env.'
            );
        }

        $intermediates = [];

        foreach ($stopAddresses as $address) {
            $intermediates[] = [
                'address' => $address,
            ];
        }

        $payload = [
            'origin' => [
                'address' => $originAddress,
            ],
            'destination' => [
                'address' => $originAddress,
            ],
            'intermediates' => $intermediates,
            'travelMode' => 'DRIVE',
            'routingPreference' => 'TRAFFIC_UNAWARE',
            'computeAlternativeRoutes' => false,
            'languageCode' => 'pt-PT',
            'units' => 'METRIC',
        ];

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        if (!is_string($json) || $json === '') {
            throw new RuntimeException(
                'Não foi possível preparar a rota.'
            );
        }

        [$status, $responseBody] =
            $this->postJson($json);

        $decoded = json_decode(
            $responseBody,
            true
        );

        if (!is_array($decoded)) {
            throw new RuntimeException(
                'Resposta inválida recebida do Google Maps.'
            );
        }

        if ($status < 200 || $status >= 300) {
            $message = trim(
                (string) (
                    $decoded['error']['message'] ??
                    'Não foi possível calcular a rota.'
                )
            );

            throw new RuntimeException($message);
        }

        $distanceMeters = (int) (
            $decoded['routes'][0]['distanceMeters'] ??
            0
        );

        if ($distanceMeters <= 0) {
            throw new RuntimeException(
                'O Google Maps não devolveu uma distância válida.'
            );
        }

        return [
            'distance_meters' => $distanceMeters,
            'distance_km' => round(
                $distanceMeters / 1000,
                1
            ),
        ];
    }

    private function postJson(string $json): array
    {
        $headers = [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $this->apiKey,
            'X-Goog-FieldMask: routes.distanceMeters',
        ];

        if (function_exists('curl_init')) {
            $curl = curl_init(self::ENDPOINT);

            if ($curl === false) {
                throw new RuntimeException(
                    'Não foi possível iniciar a ligação ao Google Maps.'
                );
            }

            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 20,
            ]);

            $body = curl_exec($curl);

            if ($body === false) {
                $error = curl_error($curl);
                curl_close($curl);

                throw new RuntimeException(
                    'Falha ao contactar Google Maps: ' .
                    $error
                );
            }

            $status = (int) curl_getinfo(
                $curl,
                CURLINFO_HTTP_CODE
            );

            curl_close($curl);

            return [
                $status,
                (string) $body,
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' =>
                    implode("\r\n", $headers),
                'content' => $json,
                'timeout' => 20,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents(
            self::ENDPOINT,
            false,
            $context
        );

        if ($body === false) {
            throw new RuntimeException(
                'Não foi possível contactar Google Maps.'
            );
        }

        $status = 0;

        foreach (($http_response_header ?? []) as $header) {
            if (
                preg_match(
                    '#^HTTP/\S+\s+(\d{3})#',
                    $header,
                    $match
                )
            ) {
                $status = (int) $match[1];
            }
        }

        return [
            $status,
            (string) $body,
        ];
    }

    private static function readApiKey(): string
    {
        $environmentValue = trim(
            (string) getenv('GOOGLE_MAPS_API_KEY')
        );

        if ($environmentValue !== '') {
            return $environmentValue;
        }

        if (!defined('BASE_PATH')) {
            return '';
        }

        $envPath = BASE_PATH . '/.env';

        if (!is_file($envPath)) {
            return '';
        }

        $lines = @file(
            $envPath,
            FILE_IGNORE_NEW_LINES
        );

        if (!is_array($lines)) {
            return '';
        }

        foreach ($lines as $line) {
            if (
                preg_match(
                    '/^\s*GOOGLE_MAPS_API_KEY\s*=\s*(.*)\s*$/',
                    $line,
                    $match
                )
            ) {
                $value = trim(
                    (string) $match[1]
                );

                if (
                    strlen($value) >= 2 &&
                    (
                        (
                            str_starts_with($value, '"') &&
                            str_ends_with($value, '"')
                        ) ||
                        (
                            str_starts_with($value, "'") &&
                            str_ends_with($value, "'")
                        )
                    )
                ) {
                    $value = substr(
                        $value,
                        1,
                        -1
                    );
                }

                return trim($value);
            }
        }

        return '';
    }
}