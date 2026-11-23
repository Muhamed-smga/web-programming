<?php

use Sarajevo2B\Backend\Services\ApiException;
use Sarajevo2B\Backend\Services\ExternalApiService;

require __DIR__ . '/../vendor/autoload.php';

function assertWeatherTest(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expectWeatherException(
    callable $action,
    int $status,
    string $expectedMessage,
    string $assertionMessage
): void
{
    try {
        $action();
    } catch (ApiException $exception) {
        assertWeatherTest(
            $exception->getStatus() === $status && $exception->getMessage() === $expectedMessage,
            $assertionMessage
        );
        return;
    }

    throw new RuntimeException($assertionMessage);
}

$config = [
    'weather_api_key' => 'test-key',
    'weather_base_url' => 'https://api.openweathermap.org/data/2.5/weather',
    'connect_timeout' => 3,
    'timeout' => 8,
];
$weatherJson = json_encode([
    'name' => 'Sarajevo',
    'main' => ['temp' => 12.4, 'feels_like' => 11.0, 'humidity' => 70],
    'weather' => [['description' => 'light rain', 'icon' => '10d']],
    'wind' => ['speed' => 3.2],
]);
$requestDetails = [];
$successService = new ExternalApiService($config, static function (
    string $url,
    int $connectTimeout,
    int $timeout
) use (&$requestDetails, $weatherJson): array {
    $requestDetails = [$url, $connectTimeout, $timeout];
    return ['status' => 200, 'body' => $weatherJson, 'error' => ''];
});
$weather = $successService->getWeather(43.8563, 18.4131);
assertWeatherTest($weather === [
    'location' => 'Sarajevo',
    'temperature' => 12.4,
    'feels_like' => 11.0,
    'humidity' => 70,
    'description' => 'light rain',
    'icon' => '10d',
    'wind_speed' => 3.2,
], 'Weather response mapping failed.');
parse_str((string) parse_url($requestDetails[0], PHP_URL_QUERY), $query);
assertWeatherTest(
    $query['units'] === 'metric'
        && $query['appid'] === 'test-key'
        && $requestDetails[1] === 3
        && $requestDetails[2] === 8,
    'The provider request omitted expected query values or timeouts.'
);

$timeoutService = new ExternalApiService($config, static function (): array {
    return ['status' => 0, 'body' => '', 'error' => 'Connection timed out.'];
});
expectWeatherException(
    static function () use ($timeoutService): void {
        $timeoutService->getWeather(43.8563, 18.4131);
    },
    504,
    'The weather service could not be reached.',
    'Network failures must return the expected HTTP 504 response.'
);

$notFoundService = new ExternalApiService($config, static function (): array {
    return ['status' => 404, 'body' => '', 'error' => ''];
});
expectWeatherException(
    static function () use ($notFoundService): void {
        $notFoundService->getWeather(43.8563, 18.4131);
    },
    502,
    'The weather service returned an error.',
    'Provider HTTP 404 must return the expected HTTP 502 response.'
);

$unauthorizedService = new ExternalApiService($config, static function (): array {
    return [
        'status' => 401,
        'body' => '{"message":"Invalid API key test-key"}',
        'error' => '',
    ];
});
$unauthorizedMessage = '';
$unauthorizedRejected = false;
try {
    $unauthorizedService->getWeather(43.8563, 18.4131);
} catch (ApiException $exception) {
    $unauthorizedRejected = true;
    assertWeatherTest(
        $exception->getStatus() === 502
            && $exception->getMessage() === 'The weather service returned an error.',
        'Provider HTTP 401 must return the generic HTTP 502 response.'
    );
    $unauthorizedMessage = $exception->getMessage();
}
assertWeatherTest(
    $unauthorizedRejected
        && strpos($unauthorizedMessage, 'test-key') === false
        && strpos($unauthorizedMessage, 'openweathermap.org') === false,
    'Provider details or the API key leaked into the client error.'
);

$serverErrorService = new ExternalApiService($config, static function (): array {
    return ['status' => 503, 'body' => '', 'error' => ''];
});
expectWeatherException(
    static function () use ($serverErrorService): void {
        $serverErrorService->getWeather(43.8563, 18.4131);
    },
    502,
    'The weather service returned an error.',
    'Other provider errors must return the expected HTTP 502 response.'
);

$invalidJsonService = new ExternalApiService($config, static function (): array {
    return ['status' => 200, 'body' => '{', 'error' => ''];
});
expectWeatherException(
    static function () use ($invalidJsonService): void {
        $invalidJsonService->getWeather(43.8563, 18.4131);
    },
    502,
    'The weather service returned an invalid response.',
    'Invalid provider JSON must return the expected HTTP 502 response.'
);

$emptyResponseService = new ExternalApiService($config, static function (): array {
    return ['status' => 200, 'body' => '', 'error' => ''];
});
expectWeatherException(
    static function () use ($emptyResponseService): void {
        $emptyResponseService->getWeather(43.8563, 18.4131);
    },
    502,
    'The weather service returned an invalid response.',
    'An empty provider response must return HTTP 502.'
);

$missingConfigService = new ExternalApiService(['weather_api_key' => '']);
expectWeatherException(
    static function () use ($missingConfigService): void {
        $missingConfigService->getWeather(43.8563, 18.4131);
    },
    500,
    'The weather service is not configured.',
    'Missing API configuration must return the expected HTTP 500 response.'
);

echo "External weather service tests passed.\n";
