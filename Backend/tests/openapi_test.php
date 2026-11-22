<?php

$baseUrl = rtrim(
    getenv('SARAJEVO2B_API_BASE_URL') ?: 'http://127.0.0.1:8000/api',
    '/'
);
$failures = 0;

function openApiCheck(bool $condition, string $message): void
{
    global $failures;

    if ($condition) {
        echo "[PASS] {$message}\n";
        return;
    }

    ++$failures;
    echo "[FAIL] {$message}\n";
}

function resolveJsonPointer(array $document, string $reference): bool
{
    if (!str_starts_with($reference, '#/')) {
        return false;
    }

    $value = $document;
    foreach (explode('/', substr($reference, 2)) as $segment) {
        $segment = str_replace(['~1', '~0'], ['/', '~'], $segment);
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return false;
        }
        $value = $value[$segment];
    }

    return true;
}

function checkReferences($value, array $document, array &$unresolved): void
{
    if (!is_array($value)) {
        return;
    }

    if (isset($value['$ref']) && is_string($value['$ref'])
        && !resolveJsonPointer($document, $value['$ref'])) {
        $unresolved[] = $value['$ref'];
    }

    foreach ($value as $item) {
        checkReferences($item, $document, $unresolved);
    }
}

function normalizeRoutePath(string $routePath): string
{
    $path = preg_replace('/@([A-Za-z_][A-Za-z0-9_]*):[^\/]+/', '{$1}', $routePath);
    if (!is_string($path)) {
        throw new RuntimeException('Could not normalize a route path.');
    }
    $path = str_replace('[.]', '.', $path);

    $path = preg_replace('#^/api(?=/|$)#', '', $path);
    if ($path === '/openapi.json') {
        $path = '/docs/openapi.json';
    }
    return $path === '' ? '/' : $path;
}

$handle = curl_init($baseUrl . '/docs/openapi.json');
curl_setopt_array($handle, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);
$body = curl_exec($handle);
$curlError = $body === false ? curl_error($handle) : '';
$status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
curl_close($handle);

openApiCheck($status === 200 && $body !== false, 'OpenAPI endpoint returns HTTP 200');
$document = $body === false ? null : json_decode($body, true);
openApiCheck(
    is_array($document) && isset($document['openapi'], $document['paths']),
    'OpenAPI endpoint returns a valid specification document'
);

if ($curlError !== '') {
    ++$failures;
    echo "[FAIL] OpenAPI endpoint request: {$curlError}\n";
}

$uiHandle = curl_init($baseUrl . '/docs');
curl_setopt_array($uiHandle, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT => 15,
]);
$uiBody = curl_exec($uiHandle);
$uiContentType = (string) curl_getinfo($uiHandle, CURLINFO_CONTENT_TYPE);
$uiStatus = (int) curl_getinfo($uiHandle, CURLINFO_RESPONSE_CODE);
curl_close($uiHandle);
openApiCheck(
    $uiStatus === 200
        && is_string($uiBody)
        && stripos($uiContentType, 'text/html') === 0
        && strpos($uiBody, 'swagger-ui-dist@5.17.14') !== false
        && strpos($uiBody, '/api/docs/openapi.json') !== false,
    'Swagger UI serves HTML with the pinned CDN and OpenAPI URL'
);

if (!is_array($document) || !isset($document['openapi'], $document['paths'])) {
    exit(1);
}

openApiCheck(
    isset($document['openapi']) && str_starts_with((string) $document['openapi'], '3.'),
    'Generated document declares OpenAPI 3.x'
);

$unresolved = [];
checkReferences($document, $document, $unresolved);
openApiCheck($unresolved === [], $unresolved === []
    ? 'All internal $ref pointers resolve'
    : 'Unresolved $ref pointers: ' . implode(', ', array_unique($unresolved)));

$weatherResponses = $document['paths']['/external/weather']['get']['responses'] ?? [];
$requiredWeatherStatuses = ['200', '400', '502', '504'];
$missingWeatherStatuses = array_diff($requiredWeatherStatuses, array_keys($weatherResponses));
openApiCheck(
    $missingWeatherStatuses === [],
    $missingWeatherStatuses === []
        ? 'Weather documents success, validation, provider, and timeout statuses'
        : 'Weather is missing response statuses: ' . implode(', ', $missingWeatherStatuses)
);

$indexPath = __DIR__ . '/../index.php';
$source = file_get_contents($indexPath);
if ($source === false) {
    ++$failures;
    echo "[FAIL] Could not read index.php for route coverage\n";
    exit(1);
}

$registeredRoutes = [];
preg_match_all(
    '~\$route\(\s*\'([A-Z]+)\'\s*,\s*\'([^\']+)\'~',
    $source,
    $directRoutes,
    PREG_SET_ORDER
);
foreach ($directRoutes as $routeMatch) {
    $registeredRoutes[] = [$routeMatch[1], normalizeRoutePath($routeMatch[2])];
}

preg_match_all(
    '~foreach\s*\(\s*\[([^\]]+)\]\s+as\s+\$method\s*\)\s*\{\s*\$route\(\s*\$method\s*,\s*\'([^\']+)\'~s',
    $source,
    $methodLoops,
    PREG_SET_ORDER
);
foreach ($methodLoops as $loopMatch) {
    preg_match_all("/'([A-Z]+)'/", $loopMatch[1], $loopMethods);
    foreach ($loopMethods[1] as $method) {
        $registeredRoutes[] = [$method, normalizeRoutePath($loopMatch[2])];
    }
}

$missingOperations = [];
foreach ($registeredRoutes as [$method, $path]) {
    $operation = strtolower($method);
    if (!isset($document['paths'][$path][$operation])) {
        $missingOperations[] = $method . ' ' . $path;
    }
}
openApiCheck(
    $missingOperations === [],
    $missingOperations === []
        ? count($registeredRoutes) . ' registered routes have documented operations'
        : 'Missing route operations: ' . implode(', ', $missingOperations)
);

echo $failures === 0
    ? "OpenAPI checks passed.\n"
    : "OpenAPI checks failed: {$failures} failure(s).\n";
exit($failures === 0 ? 0 : 1);
