<?php

namespace Sarajevo2B\Backend\Services;

use Closure;

final class ExternalApiService
{
    private array $config;
    private Closure $httpRequest;

    public function __construct(array $config, ?callable $httpRequest = null)
    {
        $this->config = $config;
        $this->httpRequest = $httpRequest === null
            ? Closure::fromCallable([$this, 'performRequest'])
            : Closure::fromCallable($httpRequest);
    }

    public function getWeather(float $lat, float $lon): array
    {
        $apiKey = trim((string) ($this->config['weather_api_key'] ?? ''));
        if ($apiKey === '') {
            throw new ApiException('The weather service is not configured.', 500);
        }

        if (!is_finite($lat) || $lat < -90 || $lat > 90 || !is_finite($lon) || $lon < -180 || $lon > 180) {
            throw new ApiException('The coordinates are invalid.', 400);
        }

        $url = (string) ($this->config['weather_base_url'] ?? '');
        $url .= '?' . http_build_query([
            'lat' => $lat,
            'lon' => $lon,
            'units' => 'metric',
            'appid' => $apiKey,
        ]);
        $response = ($this->httpRequest)(
            $url,
            (int) ($this->config['connect_timeout'] ?? 3),
            (int) ($this->config['timeout'] ?? 8)
        );

        $status = (int) ($response['status'] ?? 0);
        if (!empty($response['error']) || $status === 0) {
            throw new ApiException('The weather service could not be reached.', 504);
        }

        $body = (string) ($response['body'] ?? '');
        if ($status === 401) {
            $this->logUnauthorizedResponse($body, $apiKey);
        }
        if ($status < 200 || $status >= 300) {
            throw new ApiException('The weather service returned an error.', 502);
        }

        $weather = json_decode($body, true);
        if (trim($body) === '' || json_last_error() !== JSON_ERROR_NONE || !is_array($weather)) {
            throw new ApiException('The weather service returned an invalid response.', 502);
        }

        return $this->mapWeatherResponse($weather);
    }

    private function performRequest(string $url, int $connectTimeout, int $timeout): array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            return ['status' => 0, 'body' => '', 'error' => 'Unable to initialize cURL.'];
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_TIMEOUT => $timeout,
        ]);
        $body = curl_exec($handle);
        $error = $body === false ? curl_error($handle) : '';
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return [
            'status' => $status,
            'body' => $body === false ? '' : $body,
            'error' => $error,
        ];
    }

    private function logUnauthorizedResponse(string $body, string $apiKey): void
    {
        $providerResponse = json_decode($body, true);
        $reason = is_array($providerResponse) && is_string($providerResponse['message'] ?? null)
            ? trim($providerResponse['message'])
            : '';
        if ($reason === '') {
            $reason = 'The provider rejected the weather request.';
        }
        if ($apiKey !== '') {
            $reason = str_replace($apiKey, '[redacted]', $reason);
        }

        error_log('OpenWeatherMap returned HTTP 401: ' . $reason);
    }

    private function mapWeatherResponse(array $weather): array
    {
        $main = $weather['main'] ?? null;
        $condition = $weather['weather'][0] ?? null;
        $wind = $weather['wind'] ?? null;
        $location = $weather['name'] ?? null;

        if (
            !is_string($location) || trim($location) === ''
            || !is_array($main)
            || !is_numeric($main['temp'] ?? null) || !is_finite((float) $main['temp'])
            || !is_numeric($main['feels_like'] ?? null) || !is_finite((float) $main['feels_like'])
            || !is_numeric($main['humidity'] ?? null) || !is_finite((float) $main['humidity'])
            || !is_array($condition)
            || !is_string($condition['description'] ?? null) || trim($condition['description']) === ''
            || !is_string($condition['icon'] ?? null) || trim($condition['icon']) === ''
            || !is_array($wind)
            || !is_numeric($wind['speed'] ?? null) || !is_finite((float) $wind['speed'])
        ) {
            throw new ApiException('The weather service returned an invalid response.', 502);
        }

        return [
            'location' => trim($location),
            'temperature' => (float) $main['temp'],
            'feels_like' => (float) $main['feels_like'],
            'humidity' => (int) $main['humidity'],
            'description' => trim($condition['description']),
            'icon' => trim($condition['icon']),
            'wind_speed' => (float) $wind['speed'],
        ];
    }
}
