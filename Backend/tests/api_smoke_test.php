<?php

$baseUrl = rtrim(
    getenv('SARAJEVO2B_API_BASE_URL') ?: 'http://127.0.0.1:8000/api',
    '/'
);
$failures = 0;
$records = [
    'categories' => [],
    'places' => [],
    'events' => [],
    'reviews' => [],
    'users' => [],
];
$runId = bin2hex(random_bytes(5));

function apiRequest(
    string $baseUrl,
    string $method,
    string $path,
    ?string $body = null,
    ?string $token = null,
    ?string $contentType = 'application/json',
    ?string $origin = null,
    array $additionalHeaders = []
): array {
    $headers = ['Accept: application/json'];
    if ($token !== null && $token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ($contentType !== null) {
        $headers[] = 'Content-Type: ' . $contentType;
    }
    if ($origin !== null) {
        $headers[] = 'Origin: ' . $origin;
    }
    $headers = array_merge($headers, $additionalHeaders);

    $handle = curl_init($baseUrl . $path);
    $responseHeaders = [];
    curl_setopt_array($handle, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$responseHeaders): int {
            $separator = strpos($line, ':');
            if ($separator !== false) {
                $name = strtolower(trim(substr($line, 0, $separator)));
                $responseHeaders[$name][] = trim(substr($line, $separator + 1));
            }
            return strlen($line);
        },
    ]);
    if ($body !== null) {
        curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
    }
    $responseBody = curl_exec($handle);
    $error = $responseBody === false ? curl_error($handle) : '';
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);

    return [
        'status' => $status,
        'body' => $responseBody === false ? '' : $responseBody,
        'json' => $responseBody === false ? null : json_decode($responseBody, true),
        'error' => $error,
        'headers' => $responseHeaders,
    ];
}

function reportCase(
    string $name,
    array $response,
    int $expectedStatus,
    ?string $expectedMessage = null
): bool {
    global $failures;

    $expectedShape = $expectedStatus >= 400 ? 'error' : 'data';
    $passed = $response['status'] === $expectedStatus
        && is_array($response['json'])
        && array_key_exists($expectedShape, $response['json']);
    if ($expectedMessage !== null && $passed) {
        $passed = $expectedShape === 'error'
            ? ($response['json']['error'] ?? null) === $expectedMessage
            : ($response['json']['data']['message'] ?? null) === $expectedMessage;
    }

    if ($passed) {
        echo "[PASS] {$name}\n";
        return true;
    }

    ++$failures;
    $detail = $response['error'] !== ''
        ? $response['error']
        : 'HTTP ' . $response['status'] . ' ' . substr($response['body'], 0, 180);
    echo "[FAIL] {$name} (expected HTTP {$expectedStatus}; {$detail})\n";
    return false;
}

function reportCondition(string $name, bool $passed): void
{
    global $failures;

    if ($passed) {
        echo "[PASS] {$name}\n";
        return;
    }

    ++$failures;
    echo "[FAIL] {$name}\n";
}

function hasExpectedCorsHeaders(array $response, string $origin): bool
{
    $headers = $response['headers'] ?? [];
    return ($headers['access-control-allow-origin'][0] ?? null) === $origin
        && in_array('Origin', $headers['vary'] ?? [], true)
        && ($headers['access-control-allow-headers'][0] ?? null) === 'Content-Type, Authorization'
        && ($headers['access-control-allow-methods'][0] ?? null) === 'GET, POST, PUT, PATCH, DELETE, OPTIONS'
        && ($headers['access-control-max-age'][0] ?? null) === '600';
}

function callCase(
    string $baseUrl,
    string $name,
    string $method,
    string $path,
    int $expectedStatus,
    ?string $token = null,
    ?string $body = null,
    ?string $contentType = 'application/json',
    ?string $expectedMessage = null
): array {
    $response = apiRequest($baseUrl, $method, $path, $body, $token, $contentType);
    reportCase($name, $response, $expectedStatus, $expectedMessage);
    return $response;
}

function jsonBody(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function trackRecord(array &$records, string $entity, array $response): ?int
{
    $id = $response['json']['data']['id'] ?? null;
    if (is_numeric($id) && (int) $id > 0) {
        $records[$entity][] = (int) $id;
        return (int) $id;
    }

    return null;
}

function removeTrackedRecord(array &$records, string $entity, int $id): void
{
    $records[$entity] = array_values(array_filter(
        $records[$entity],
        static function (int $trackedId) use ($id): bool {
            return $trackedId !== $id;
        }
    ));
}

function deleteTrackedRecord(
    string $baseUrl,
    array &$records,
    string $entity,
    int $id,
    string $message,
    string $token,
    string $caseName
): bool {
    $response = callCase(
        $baseUrl,
        $caseName,
        'DELETE',
        '/' . $entity . '/' . $id,
        200,
        $token,
        null,
        'application/json',
        $message
    );
    if ($response['status'] === 200) {
        removeTrackedRecord($records, $entity, $id);
        return true;
    }

    return false;
}

function testInvalidIds(
    string $baseUrl,
    string $entity,
    ?string $token = null
): void {
    foreach (['abc', '0'] as $invalidId) {
        callCase(
            $baseUrl,
            ucfirst($entity) . " invalid ID {$invalidId}",
            'GET',
            '/' . $entity . '/' . $invalidId,
            400,
            $token
        );
    }
}

function testMissingRecord(
    string $baseUrl,
    string $entity,
    ?string $token = null
): void {
    callCase(
        $baseUrl,
        ucfirst($entity) . ' nonexistent ID',
        'GET',
        '/' . $entity . '/2147483000',
        404,
        $token
    );
}

callCase($baseUrl, 'Health endpoint', 'GET', '/health', 200);
callCase($baseUrl, 'Weather requires both coordinates', 'GET', '/external/weather?lat=43.8563', 400);
callCase($baseUrl, 'Unknown endpoint returns JSON 404', 'GET', '/does-not-exist', 404);
callCase(
    $baseUrl,
    'Unsupported method returns JSON error',
    'DELETE',
    '/health',
    400
);

$allowedOrigin = 'http://localhost';
$disallowedOrigin = 'https://not-allowed.example';
$allowedOriginNotFound = apiRequest(
    $baseUrl,
    'GET',
    '/does-not-exist',
    null,
    null,
    null,
    $allowedOrigin
);
reportCase('Allowed-origin 404 status', $allowedOriginNotFound, 404);
reportCondition(
    'Allowed origin receives CORS headers on 404',
    hasExpectedCorsHeaders($allowedOriginNotFound, $allowedOrigin)
);

$allowedOriginBadRequest = apiRequest(
    $baseUrl,
    'GET',
    '/external/weather?lat=43.8563',
    null,
    null,
    null,
    $allowedOrigin
);
reportCase('Allowed-origin validation status', $allowedOriginBadRequest, 400);
reportCondition(
    'Allowed origin receives CORS headers on 400',
    hasExpectedCorsHeaders($allowedOriginBadRequest, $allowedOrigin)
);

$allowedOriginUnauthorized = apiRequest(
    $baseUrl,
    'GET',
    '/me',
    null,
    null,
    null,
    $allowedOrigin
);
reportCase('Allowed-origin unauthorized status', $allowedOriginUnauthorized, 401);
reportCondition(
    'Allowed origin receives CORS headers on 401',
    hasExpectedCorsHeaders($allowedOriginUnauthorized, $allowedOrigin)
);

$disallowedOriginResponse = apiRequest(
    $baseUrl,
    'GET',
    '/health',
    null,
    null,
    null,
    $disallowedOrigin
);
reportCase('Disallowed-origin request still works', $disallowedOriginResponse, 200);
$corsHeaders = [
    'access-control-allow-origin',
    'vary',
    'access-control-allow-headers',
    'access-control-allow-methods',
    'access-control-max-age',
];
reportCondition(
    'Disallowed origin receives no CORS headers',
    count(array_intersect($corsHeaders, array_keys($disallowedOriginResponse['headers'] ?? []))) === 0
);

$preflight = apiRequest(
    $baseUrl,
    'OPTIONS',
    '/external/weather',
    null,
    null,
    null,
    $allowedOrigin,
    [
        'Access-Control-Request-Method: GET',
        'Access-Control-Request-Headers: content-type,authorization',
    ]
);
reportCondition(
    'Weather preflight succeeds with CORS headers and empty body',
    $preflight['status'] >= 200
        && $preflight['status'] < 300
        && $preflight['body'] === ''
        && hasExpectedCorsHeaders($preflight, $allowedOrigin)
);

$adminLogin = callCase(
    $baseUrl,
    'Admin login',
    'POST',
    '/auth/login',
    200,
    null,
    jsonBody(['email' => 'admin@sarajevo2b.com', 'password' => 'Admin123!'])
);
$adminToken = is_array($adminLogin['json']['data'] ?? null)
    ? (string) ($adminLogin['json']['data']['token'] ?? '')
    : '';
$adminId = (int) ($adminLogin['json']['data']['user']['id'] ?? 0);

callCase(
    $baseUrl,
    'Seed regular-user login',
    'POST',
    '/auth/login',
    200,
    null,
    jsonBody(['email' => 'user@sarajevo2b.com', 'password' => 'User123!'])
);

$smokeEmail = 'smoke-' . $runId . '@example.test';
$registeredUser = callCase(
    $baseUrl,
    'Register a regular user',
    'POST',
    '/auth/register',
    201,
    null,
    jsonBody(['name' => 'Smoke User', 'email' => $smokeEmail, 'password' => 'SmokeUser123!'])
);
$registeredUserId = trackRecord($records, 'users', $registeredUser);
$userToken = (string) ($registeredUser['json']['data']['token'] ?? '');
if ($registeredUserId === null && isset($registeredUser['json']['data']['id'])) {
    $registeredUserId = (int) $registeredUser['json']['data']['id'];
    $records['users'][] = $registeredUserId;
}
if ($userToken === '') {
    $userLogin = callCase(
        $baseUrl,
        'Login a regular user',
        'POST',
        '/auth/login',
        200,
        null,
        jsonBody(['email' => $smokeEmail, 'password' => 'SmokeUser123!'])
    );
    $userToken = (string) ($userLogin['json']['data']['token'] ?? '');
}
$allowedOriginForbidden = apiRequest(
    $baseUrl,
    'GET',
    '/users',
    null,
    $userToken,
    null,
    $allowedOrigin
);
reportCase('Allowed-origin forbidden status', $allowedOriginForbidden, 403);
reportCondition(
    'Allowed origin receives CORS headers on 403 from middleware',
    hasExpectedCorsHeaders($allowedOriginForbidden, $allowedOrigin)
);
callCase(
    $baseUrl,
    'Wrong password returns 401',
    'POST',
    '/auth/login',
    401,
    null,
    jsonBody(['email' => $smokeEmail, 'password' => 'WrongPassword123!'])
);
callCase(
    $baseUrl,
    'Duplicate email returns 400',
    'POST',
    '/auth/register',
    400,
    null,
    jsonBody(['name' => 'Duplicate', 'email' => $smokeEmail, 'password' => 'SmokeUser123!']),
    'application/json',
    'Email is already registered.'
);
callCase($baseUrl, 'GET /me', 'GET', '/me', 200, $userToken);
callCase(
    $baseUrl,
    'PATCH /me',
    'PATCH',
    '/me',
    200,
    $userToken,
    jsonBody(['name' => 'Smoke User Updated'])
);

$categoryPayload = [
    'name' => 'Smoke Category ' . $runId,
    'description' => 'Temporary API smoke-test category',
];
$categoryCreate = callCase(
    $baseUrl,
    'Categories create returns 201',
    'POST',
    '/categories',
    201,
    $adminToken,
    jsonBody($categoryPayload)
);
$categoryId = trackRecord($records, 'categories', $categoryCreate);

callCase($baseUrl, 'Categories list returns 200', 'GET', '/categories', 200);
if ($categoryId !== null) {
    callCase($baseUrl, 'Categories get one returns 200', 'GET', '/categories/' . $categoryId, 200);
}
testInvalidIds($baseUrl, 'categories');
testMissingRecord($baseUrl, 'categories');
callCase(
    $baseUrl,
    'Categories missing token returns 401',
    'POST',
    '/categories',
    401,
    null,
    jsonBody($categoryPayload)
);
callCase(
    $baseUrl,
    'Categories regular user returns 403',
    'POST',
    '/categories',
    403,
    $userToken,
    jsonBody($categoryPayload)
);
callCase(
    $baseUrl,
    'Categories invalid JSON returns 400',
    'POST',
    '/categories',
    400,
    $adminToken,
    '{'
);
callCase(
    $baseUrl,
    'Categories missing required field returns 400',
    'POST',
    '/categories',
    400,
    $adminToken,
    '{}'
);
callCase(
    $baseUrl,
    'Categories invalid field value returns 400',
    'POST',
    '/categories',
    400,
    $adminToken,
    jsonBody(['name' => 12])
);
callCase(
    $baseUrl,
    'Invalid Content-Type returns 400',
    'POST',
    '/categories',
    400,
    $adminToken,
    '{}',
    'text/plain'
);
callCase(
    $baseUrl,
    'Empty POST body returns 400',
    'POST',
    '/categories',
    400,
    $adminToken,
    ''
);
if ($categoryId !== null) {
    callCase(
        $baseUrl,
        'Empty PUT body returns 400',
        'PUT',
        '/categories/' . $categoryId,
        400,
        $adminToken,
        ''
    );
    callCase(
        $baseUrl,
        'Categories missing PUT fields returns 400',
        'PUT',
        '/categories/' . $categoryId,
        400,
        $adminToken,
        '{}'
    );
    callCase(
        $baseUrl,
        'Categories update returns 200',
        'PUT',
        '/categories/' . $categoryId,
        200,
        $adminToken,
        jsonBody([
            'name' => 'Smoke Category Updated ' . $runId,
            'description' => 'Updated temporary category',
        ])
    );
}

$placePayload = [
    'category_id' => $categoryId,
    'name' => 'Smoke Place ' . $runId,
    'address' => 'Sarajevo',
    'description' => 'Temporary API smoke-test place',
    'latitude' => 43.8563,
    'longitude' => 18.4131,
];
$placeCreate = callCase(
    $baseUrl,
    'Places create returns 201',
    'POST',
    '/places',
    201,
    $adminToken,
    jsonBody($placePayload)
);
$placeId = trackRecord($records, 'places', $placeCreate);
callCase($baseUrl, 'Places list returns 200', 'GET', '/places', 200);
if ($placeId !== null) {
    callCase($baseUrl, 'Places get one returns 200', 'GET', '/places/' . $placeId, 200);
}
testInvalidIds($baseUrl, 'places');
testMissingRecord($baseUrl, 'places');
callCase(
    $baseUrl,
    'Places missing token returns 401',
    'POST',
    '/places',
    401,
    null,
    jsonBody($placePayload)
);
callCase(
    $baseUrl,
    'Places regular user returns 403',
    'POST',
    '/places',
    403,
    $userToken,
    jsonBody($placePayload)
);
callCase(
    $baseUrl,
    'Places invalid JSON returns 400',
    'POST',
    '/places',
    400,
    $adminToken,
    '{'
);
callCase(
    $baseUrl,
    'Places missing required field returns 400',
    'POST',
    '/places',
    400,
    $adminToken,
    jsonBody(['name' => 'Missing place fields'])
);
callCase(
    $baseUrl,
    'Places invalid field value returns 400',
    'POST',
    '/places',
    400,
    $adminToken,
    jsonBody([
        'category_id' => 'invalid',
        'name' => 'Invalid place',
        'address' => 'Sarajevo',
    ])
);
if ($placeId !== null && $categoryId !== null) {
    callCase(
        $baseUrl,
        'Places update returns 200',
        'PUT',
        '/places/' . $placeId,
        200,
        $adminToken,
        jsonBody([
            'category_id' => $categoryId,
            'name' => 'Smoke Place Updated ' . $runId,
            'description' => 'Updated temporary place',
            'address' => 'Sarajevo',
            'latitude' => 43.8563,
            'longitude' => 18.4131,
            'phone' => null,
            'website' => null,
            'opening_hours' => null,
            'image_url' => null,
        ])
    );
}

$eventPayload = [
    'place_id' => $placeId,
    'title' => 'Smoke Event ' . $runId,
    'description' => 'Temporary API smoke-test event',
    'event_date' => '2027-05-15',
    'start_time' => '18:00',
    'end_time' => '19:00',
];
$eventCreate = callCase(
    $baseUrl,
    'Events create returns 201',
    'POST',
    '/events',
    201,
    $adminToken,
    jsonBody($eventPayload)
);
$eventId = trackRecord($records, 'events', $eventCreate);
callCase($baseUrl, 'Events list returns 200', 'GET', '/events', 200);
if ($eventId !== null) {
    callCase($baseUrl, 'Events get one returns 200', 'GET', '/events/' . $eventId, 200);
}
testInvalidIds($baseUrl, 'events');
testMissingRecord($baseUrl, 'events');
callCase(
    $baseUrl,
    'Events missing token returns 401',
    'POST',
    '/events',
    401,
    null,
    jsonBody($eventPayload)
);
callCase(
    $baseUrl,
    'Events regular user returns 403',
    'POST',
    '/events',
    403,
    $userToken,
    jsonBody($eventPayload)
);
callCase(
    $baseUrl,
    'Events invalid JSON returns 400',
    'POST',
    '/events',
    400,
    $adminToken,
    '{'
);
callCase(
    $baseUrl,
    'Events missing required field returns 400',
    'POST',
    '/events',
    400,
    $adminToken,
    jsonBody(['title' => 'Missing event date'])
);
callCase(
    $baseUrl,
    'Events invalid field value returns 400',
    'POST',
    '/events',
    400,
    $adminToken,
    jsonBody(['title' => 'Invalid event', 'event_date' => 'not-a-date'])
);
callCase(
    $baseUrl,
    'Event end before start returns 400',
    'POST',
    '/events',
    400,
    $adminToken,
    jsonBody([
        'title' => 'Invalid time range',
        'event_date' => '2027-05-15',
        'start_time' => '20:00',
        'end_time' => '19:00',
    ])
);
if ($eventId !== null) {
    callCase(
        $baseUrl,
        'Events update returns 200',
        'PUT',
        '/events/' . $eventId,
        200,
        $adminToken,
        jsonBody([
            'place_id' => $placeId,
            'title' => 'Smoke Event Updated ' . $runId,
            'description' => 'Updated temporary event',
            'event_date' => '2027-05-16',
            'start_time' => '18:00',
            'end_time' => '19:00',
            'image_url' => null,
        ])
    );
}

$secondUserEmail = 'smoke-owner-' . $runId . '@example.test';
$secondUser = callCase(
    $baseUrl,
    'Register a second review owner',
    'POST',
    '/auth/register',
    201,
    null,
    jsonBody([
        'name' => 'Smoke Second User',
        'email' => $secondUserEmail,
        'password' => 'SmokeUser123!',
    ])
);
trackRecord($records, 'users', $secondUser);
$secondUserLogin = callCase(
    $baseUrl,
    'Login second review owner',
    'POST',
    '/auth/login',
    200,
    null,
    jsonBody(['email' => $secondUserEmail, 'password' => 'SmokeUser123!'])
);
$secondUserToken = (string) ($secondUserLogin['json']['data']['token'] ?? '');

$reviewPayload = [
    'place_id' => $placeId,
    'rating' => 5,
    'comment' => 'Temporary API smoke-test review',
];
$reviewCreate = callCase(
    $baseUrl,
    'Reviews create returns 201',
    'POST',
    '/reviews',
    201,
    $userToken,
    jsonBody($reviewPayload)
);
$reviewId = trackRecord($records, 'reviews', $reviewCreate);
callCase($baseUrl, 'Reviews list returns 200', 'GET', '/reviews', 200);
if ($reviewId !== null) {
    callCase($baseUrl, 'Reviews get one returns 200', 'GET', '/reviews/' . $reviewId, 200);
}
testInvalidIds($baseUrl, 'reviews');
testMissingRecord($baseUrl, 'reviews');
callCase(
    $baseUrl,
    'Reviews missing token returns 401',
    'POST',
    '/reviews',
    401,
    null,
    jsonBody($reviewPayload)
);
callCase(
    $baseUrl,
    'Reviews invalid JSON returns 400',
    'POST',
    '/reviews',
    400,
    $userToken,
    '{'
);
callCase(
    $baseUrl,
    'Reviews missing required field returns 400',
    'POST',
    '/reviews',
    400,
    $userToken,
    jsonBody(['place_id' => $placeId])
);
callCase(
    $baseUrl,
    'Reviews invalid field value returns 400',
    'POST',
    '/reviews',
    400,
    $userToken,
    jsonBody(['place_id' => $placeId, 'rating' => 6])
);
callCase(
    $baseUrl,
    'Duplicate review returns 400',
    'POST',
    '/reviews',
    400,
    $userToken,
    jsonBody($reviewPayload)
);
if ($reviewId !== null) {
    callCase(
        $baseUrl,
        'Review owner update returns 200',
        'PUT',
        '/reviews/' . $reviewId,
        200,
        $userToken,
        jsonBody(['rating' => 4, 'comment' => 'Updated by owner'])
    );
    callCase(
        $baseUrl,
        'Other user cannot edit review',
        'PATCH',
        '/reviews/' . $reviewId,
        403,
        $secondUserToken,
        jsonBody(['rating' => 3])
    );
    callCase(
        $baseUrl,
        'Other user cannot delete review',
        'DELETE',
        '/reviews/' . $reviewId,
        403,
        $secondUserToken
    );
    callCase(
        $baseUrl,
        'Admin can moderate review',
        'PATCH',
        '/reviews/' . $reviewId,
        200,
        $adminToken,
        jsonBody(['rating' => 3])
    );
}

callCase($baseUrl, 'Users list returns 200', 'GET', '/users', 200, $adminToken);
callCase($baseUrl, 'Users missing token returns 401', 'GET', '/users', 401);
callCase($baseUrl, 'Users regular user returns 403', 'GET', '/users', 403, $userToken);
testInvalidIds($baseUrl, 'users', $adminToken);
testMissingRecord($baseUrl, 'users', $adminToken);
callCase(
    $baseUrl,
    'Users invalid JSON returns 400',
    'POST',
    '/users',
    400,
    $adminToken,
    '{'
);
callCase(
    $baseUrl,
    'Users missing required field returns 400',
    'POST',
    '/users',
    400,
    $adminToken,
    jsonBody(['name' => 'Missing user fields'])
);
callCase(
    $baseUrl,
    'Users invalid field value returns 400',
    'POST',
    '/users',
    400,
    $adminToken,
    jsonBody([
        'name' => 'Invalid user',
        'email' => 'not-an-email',
        'password' => 'SmokeUser123!',
        'role' => 'user',
    ])
);
$userCrudEmail = 'smoke-crud-' . $runId . '@example.test';
$userCrud = callCase(
    $baseUrl,
    'Users create returns 201',
    'POST',
    '/users',
    201,
    $adminToken,
    jsonBody([
        'name' => 'Smoke CRUD User',
        'email' => $userCrudEmail,
        'password' => 'SmokeUser123!',
        'role' => 'user',
    ])
);
$userCrudId = trackRecord($records, 'users', $userCrud);
if ($userCrudId !== null) {
    callCase($baseUrl, 'Users get one returns 200', 'GET', '/users/' . $userCrudId, 200, $adminToken);
    callCase(
        $baseUrl,
        'Users update returns 200',
        'PUT',
        '/users/' . $userCrudId,
        200,
        $adminToken,
        jsonBody([
            'name' => 'Smoke CRUD User Updated',
            'email' => $userCrudEmail,
            'role' => 'user',
        ])
    );
    deleteTrackedRecord(
        $baseUrl,
        $records,
        'users',
        $userCrudId,
        'User deleted successfully.',
        $adminToken,
        'Users delete returns 200'
    );
}

$adminBEmail = 'smoke-admin-b-' . $runId . '@example.test';
$adminB = callCase(
    $baseUrl,
    'Create temporary administrator B',
    'POST',
    '/users',
    201,
    $adminToken,
    jsonBody([
        'name' => 'Smoke Admin B',
        'email' => $adminBEmail,
        'password' => 'SmokeAdmin123!',
        'role' => 'admin',
    ])
);
$adminBId = trackRecord($records, 'users', $adminB);
$adminBToken = '';
if ($adminBId !== null) {
    $adminBLogin = callCase(
        $baseUrl,
        'Login temporary administrator B',
        'POST',
        '/auth/login',
        200,
        null,
        jsonBody(['email' => $adminBEmail, 'password' => 'SmokeAdmin123!'])
    );
    $adminBToken = (string) ($adminBLogin['json']['data']['token'] ?? '');
}
$adminCEmail = 'smoke-admin-c-' . $runId . '@example.test';
$adminC = callCase(
    $baseUrl,
    'Create temporary administrator C',
    'POST',
    '/users',
    201,
    $adminToken,
    jsonBody([
        'name' => 'Smoke Admin C',
        'email' => $adminCEmail,
        'password' => 'SmokeAdmin123!',
        'role' => 'admin',
    ])
);
$adminCId = trackRecord($records, 'users', $adminC);

if ($categoryId !== null && $placeId !== null) {
    callCase(
        $baseUrl,
        'Category in use cannot be deleted',
        'DELETE',
        '/categories/' . $categoryId,
        400,
        $adminToken
    );
}

if ($adminId > 0 && $adminBId !== null && $adminCId !== null && $adminBToken !== '') {
    $demoteSeedAdmin = callCase(
        $baseUrl,
        'Temporarily demote seed admin for last-admin test',
        'PUT',
        '/users/' . $adminId,
        200,
        $adminBToken,
        jsonBody([
            'name' => 'Admin',
            'email' => 'admin@sarajevo2b.com',
            'role' => 'user',
        ])
    );
    if ($demoteSeedAdmin['status'] === 200) {
        $deleteAdminC = deleteTrackedRecord(
            $baseUrl,
            $records,
            'users',
            $adminCId,
            'User deleted successfully.',
            $adminBToken,
            'Delete extra administrator for last-admin check'
        );
        if ($deleteAdminC) {
            callCase(
                $baseUrl,
                'Last administrator cannot be deleted',
                'DELETE',
                '/users/' . $adminBId,
                400,
                $adminBToken,
                null,
                'application/json',
                'The last administrator cannot be deleted.'
            );
        } else {
            reportCase(
                'Last administrator cannot be deleted',
                ['status' => 0, 'body' => '', 'json' => null, 'error' => 'Could not prepare last-admin test.'],
                400,
                'The last administrator cannot be deleted.'
            );
        }
        $restoreSeedAdmin = callCase(
            $baseUrl,
            'Restore seed administrator role',
            'PUT',
            '/users/' . $adminId,
            200,
            $adminBToken,
            jsonBody([
                'name' => 'Admin',
                'email' => 'admin@sarajevo2b.com',
                'role' => 'admin',
            ])
        );
        if ($restoreSeedAdmin['status'] === 200) {
            deleteTrackedRecord(
                $baseUrl,
                $records,
                'users',
                $adminBId,
                'User deleted successfully.',
                $adminBToken,
                'Remove temporary administrator B'
            );
        }
    }
} else {
    reportCase(
        'Last administrator cannot be deleted',
        ['status' => 0, 'body' => '', 'json' => null, 'error' => 'Could not create temporary administrators.'],
        400,
        'The last administrator cannot be deleted.'
    );
}

if ($reviewId !== null) {
    deleteTrackedRecord(
        $baseUrl,
        $records,
        'reviews',
        $reviewId,
        'Review deleted successfully.',
        $adminToken,
        'Reviews delete returns 200'
    );
}
foreach (array_reverse($records['events']) as $id) {
    deleteTrackedRecord(
        $baseUrl,
        $records,
        'events',
        $id,
        'Event deleted successfully.',
        $adminToken,
        'Clean up event ' . $id
    );
}
foreach (array_reverse($records['places']) as $id) {
    deleteTrackedRecord(
        $baseUrl,
        $records,
        'places',
        $id,
        'Place deleted successfully.',
        $adminToken,
        'Places delete returns 200'
    );
}
foreach (array_reverse($records['categories']) as $id) {
    deleteTrackedRecord(
        $baseUrl,
        $records,
        'categories',
        $id,
        'Category deleted successfully.',
        $adminToken,
        'Categories delete returns 200'
    );
}
foreach (array_reverse($records['users']) as $id) {
    deleteTrackedRecord(
        $baseUrl,
        $records,
        'users',
        $id,
        'User deleted successfully.',
        $adminToken,
        'Clean up user ' . $id
    );
}

echo $failures === 0
    ? "Smoke test completed: all cases passed.\n"
    : "Smoke test completed: {$failures} case(s) failed.\n";
exit($failures === 0 ? 0 : 1);
