<?php

use Sarajevo2B\Backend\Config\Database;
use Sarajevo2B\Backend\DAO\CategoryDAO;
use Sarajevo2B\Backend\DAO\EventDAO;
use Sarajevo2B\Backend\DAO\PlaceDAO;
use Sarajevo2B\Backend\DAO\ReviewDAO;
use Sarajevo2B\Backend\DAO\UserDAO;
use Sarajevo2B\Backend\Middleware\AuthMiddleware;
use Sarajevo2B\Backend\Middleware\RoleMiddleware;
use Sarajevo2B\Backend\Services\ApiException;
use Sarajevo2B\Backend\Services\AuthService;
use Sarajevo2B\Backend\Services\CategoryService;
use Sarajevo2B\Backend\Services\EventService;
use Sarajevo2B\Backend\Services\ExternalApiService;
use Sarajevo2B\Backend\Services\PlaceService;
use Sarajevo2B\Backend\Services\ReviewService;
use Sarajevo2B\Backend\Services\UserService;

require __DIR__ . '/vendor/autoload.php';

Flight::map('error', static function (Throwable $exception): void {
    if ($exception instanceof ApiException) {
        Flight::jsonHalt(['error' => $exception->getMessage()], $exception->getStatus());
        return;
    }

    if ($exception instanceof PDOException && $exception->getCode() === '23000') {
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        if ($driverCode === 1062) {
            Flight::jsonHalt(['error' => 'A record with the same unique value already exists.'], 400);
            return;
        }
        if ($driverCode === 1452) {
            Flight::jsonHalt(['error' => 'A referenced record does not exist.'], 400);
            return;
        }
        if ($driverCode === 1451) {
            Flight::jsonHalt(['error' => 'This record is still referenced and cannot be deleted.'], 400);
            return;
        }
    }

    if ($exception instanceof \InvalidArgumentException) {
        Flight::jsonHalt(['error' => $exception->getMessage()], 400);
        return;
    }

    error_log((string) $exception);
    Flight::jsonHalt(['error' => 'An unexpected server error occurred.'], 500);
});

Flight::map('notFound', static function (): void {
    Flight::json(['error' => 'Endpoint not found.'], 404);
});

Flight::map('methodNotFound', static function (): void {
    Flight::jsonHalt(['error' => 'The HTTP method is not supported for this endpoint.'], 400);
});

try {
    $config = require __DIR__ . '/config/config.php';
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowedOrigins = $config['cors']['allowed_origins'] ?? [];
    if (is_string($origin) && is_array($allowedOrigins) && in_array($origin, $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin', false);
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Max-Age: 600');
    }

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS'
        && is_string($requestPath)
        && ($requestPath === '/api' || str_starts_with($requestPath, '/api/'))) {
        http_response_code(204);
        exit;
    }

    if (strlen($config['jwt']['secret']) < 32) {
        throw new RuntimeException('SARAJEVO2B_JWT_SECRET must contain at least 32 characters.');
    }
    $pdo = Database::connection($config);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'The API could not initialize. Check database and JWT configuration.']);
    exit;
}

$userDAO = new UserDAO($pdo);
$userService = new UserService($userDAO);
$authService = new AuthService($userDAO, $config['jwt']['secret'], (int) $config['jwt']['ttl']);
$authMiddleware = new AuthMiddleware($authService);
$adminMiddleware = new RoleMiddleware('admin');
$categoryService = new CategoryService(new CategoryDAO($pdo));
$placeDAO = new PlaceDAO($pdo);
$placeService = new PlaceService($placeDAO);
$eventService = new EventService(new EventDAO($pdo), $placeDAO);
$reviewService = new ReviewService(new ReviewDAO($pdo), $placeDAO);
$externalApiService = new ExternalApiService($config['external'] ?? []);

$readBody = static function (): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (!preg_match('/^\s*application\/json(?:\s*;|$)/i', $contentType)) {
        throw new ApiException('Content-Type must be application/json.', 400);
    }

    $body = file_get_contents('php://input');
    if ($body === '') {
        return [];
    }

    $decoded = json_decode($body);
    if (!is_object($decoded)) {
        throw new ApiException('The request body must be a JSON object.', 400);
    }

    return get_object_vars($decoded);
};

$route = static function (string $method, string $path, callable $handler, array $middleware = []): void {
    $registered = Flight::route($method . ' ' . $path, $handler);
    foreach ($middleware as $item) {
        $registered->addMiddleware($item);
    }
};

$json = static function ($data, int $status = 200): void {
    Flight::json(['data' => $data], $status);
};

$id = static function (string $rawId): int {
    if (!ctype_digit($rawId) || (int) $rawId < 1) {
        throw new ApiException('The ID must be a positive integer.', 400);
    }
    return (int) $rawId;
};

$currentUser = static function (): array {
    $user = Flight::get('currentUser');
    if (!is_array($user)) {
        throw new ApiException('Authentication is required.', 401);
    }
    return $user;
};

$isOwnerOrAdmin = static function (array $user, int $ownerId): void {
    if ((int) $user['id'] !== $ownerId && $user['role'] !== 'admin') {
        throw new ApiException('You are not authorized to modify this record.', 403);
    }
};

$route('GET', '/api/health', static function () use ($json): void {
    $json(['status' => 'ok']);
});

$route('GET', '/api/external/weather', static function () use (
    $placeService,
    $externalApiService,
    $json
): void {
    $hasPlaceId = array_key_exists('place_id', $_GET);
    $hasLatitude = array_key_exists('lat', $_GET);
    $hasLongitude = array_key_exists('lon', $_GET);

    if ($hasPlaceId && ($hasLatitude || $hasLongitude)) {
        throw new ApiException('Use either place_id or lat and lon.', 400);
    }

    if ($hasPlaceId) {
        $placeId = $_GET['place_id'];
        if (!is_string($placeId) || !ctype_digit($placeId) || (int) $placeId < 1) {
            throw new ApiException('The place_id must be a positive integer.', 400);
        }
        $place = $placeService->getById((int) $placeId);
        if ($place['latitude'] === null || $place['longitude'] === null
            || !is_numeric($place['latitude']) || !is_numeric($place['longitude'])) {
            throw new ApiException('This place has no coordinates.', 400);
        }
        $coordinates = [(float) $place['latitude'], (float) $place['longitude']];
    } elseif ($hasLatitude || $hasLongitude) {
        if (!$hasLatitude || !$hasLongitude || !is_string($_GET['lat']) || !is_string($_GET['lon'])
            || !is_numeric($_GET['lat']) || !is_numeric($_GET['lon'])) {
            throw new ApiException('Both lat and lon must be valid numbers.', 400);
        }
        $coordinates = [(float) $_GET['lat'], (float) $_GET['lon']];
    } else {
        $coordinates = [43.8563, 18.4131];
    }

    $json($externalApiService->getWeather($coordinates[0], $coordinates[1]));
});

$route('POST', '/api/auth/register', static function () use ($readBody, $userService, $json): void {
    $json($userService->register($readBody()), 201);
});

$route('POST', '/api/auth/login', static function () use ($readBody, $authService, $json): void {
    $json($authService->login($readBody()));
});

$route('GET', '/api/me', static function () use ($currentUser, $json): void {
    $json($currentUser());
}, [$authMiddleware]);

$route('PATCH', '/api/me', static function () use ($currentUser, $readBody, $userService, $json): void {
    $user = $currentUser();
    $json($userService->update((int) $user['id'], $readBody(), false, (int) $user['id']));
}, [$authMiddleware]);

$route('GET', '/api/categories', static function () use ($categoryService, $json): void {
    $json($categoryService->getAll());
});
$route('GET', '/api/categories/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $categoryService, $json): void {
    $json($categoryService->getById($id($rawId)));
});
$route('POST', '/api/categories', static function () use ($readBody, $categoryService, $json): void {
    $json($categoryService->create($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/categories/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
        $id,
        $readBody,
        $categoryService,
        $json,
        $method
    ): void {
        $json($categoryService->update($id($rawId), $readBody(), $method === 'PUT'));
    }, [$authMiddleware, $adminMiddleware]);
}
$route('DELETE', '/api/categories/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $categoryService, $json): void {
    $categoryService->delete($id($rawId));
    $json(['message' => 'Category deleted successfully.']);
}, [$authMiddleware, $adminMiddleware]);

$route('GET', '/api/places', static function () use ($placeService, $json): void {
    $json($placeService->getAll());
});
$route('GET', '/api/places/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $placeService, $json): void {
    $json($placeService->getById($id($rawId)));
});
$route('POST', '/api/places', static function () use ($readBody, $placeService, $json): void {
    $json($placeService->create($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/places/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
        $id,
        $readBody,
        $placeService,
        $json,
        $method
    ): void {
        $json($placeService->update($id($rawId), $readBody(), $method === 'PUT'));
    }, [$authMiddleware, $adminMiddleware]);
}
$route('DELETE', '/api/places/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $placeService, $json): void {
    $placeService->delete($id($rawId));
    $json(['message' => 'Place deleted successfully.']);
}, [$authMiddleware, $adminMiddleware]);

$route('GET', '/api/events', static function () use ($eventService, $json): void {
    $json($eventService->getAll());
});
$route('GET', '/api/events/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $eventService, $json): void {
    $json($eventService->getById($id($rawId)));
});
$route('POST', '/api/events', static function () use ($readBody, $eventService, $json): void {
    $json($eventService->create($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/events/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
        $id,
        $readBody,
        $eventService,
        $json,
        $method
    ): void {
        $json($eventService->update($id($rawId), $readBody(), $method === 'PUT'));
    }, [$authMiddleware, $adminMiddleware]);
}
$route('DELETE', '/api/events/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $eventService, $json): void {
    $eventService->delete($id($rawId));
    $json(['message' => 'Event deleted successfully.']);
}, [$authMiddleware, $adminMiddleware]);

$route('GET', '/api/reviews', static function () use ($reviewService, $json): void {
    $json($reviewService->getAll());
});
$route('GET', '/api/reviews/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $reviewService, $json): void {
    $json($reviewService->getById($id($rawId)));
});
$route('POST', '/api/reviews', static function () use ($currentUser, $readBody, $reviewService, $json): void {
    $user = $currentUser();
    $json($reviewService->create((int) $user['id'], $readBody()), 201);
}, [$authMiddleware]);
$route('PUT', '/api/reviews/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
    $id,
    $currentUser,
    $readBody,
    $reviewService,
    $isOwnerOrAdmin,
    $json
): void {
    $reviewId = $id($rawId);
    $user = $currentUser();
    $review = $reviewService->getById($reviewId);
    $isOwnerOrAdmin($user, (int) $review['user_id']);
    $data = $readBody();
    \Sarajevo2B\Backend\Services\Input::requireKeys($data, ['rating', 'comment']);
    $json($reviewService->update($reviewId, $data));
}, [$authMiddleware]);
$route('PATCH', '/api/reviews/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
    $id,
    $currentUser,
    $readBody,
    $reviewService,
    $isOwnerOrAdmin,
    $json
): void {
    $reviewId = $id($rawId);
    $user = $currentUser();
    $review = $reviewService->getById($reviewId);
    $isOwnerOrAdmin($user, (int) $review['user_id']);
    $json($reviewService->update($reviewId, $readBody()));
}, [$authMiddleware]);
$route('DELETE', '/api/reviews/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
    $id,
    $currentUser,
    $reviewService,
    $isOwnerOrAdmin,
    $json
): void {
    $reviewId = $id($rawId);
    $user = $currentUser();
    $review = $reviewService->getById($reviewId);
    $isOwnerOrAdmin($user, (int) $review['user_id']);
    $reviewService->delete($reviewId);
    $json(['message' => 'Review deleted successfully.']);
}, [$authMiddleware]);

$route('GET', '/api/users', static function () use ($userService, $json): void {
    $json($userService->getAll());
}, [$authMiddleware, $adminMiddleware]);
$route('POST', '/api/users', static function () use ($readBody, $userService, $json): void {
    $json($userService->createByAdmin($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
$route('GET', '/api/users/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
    $id,
    $currentUser,
    $userService,
    $json,
    $isOwnerOrAdmin
): void {
    $userId = $id($rawId);
    $isOwnerOrAdmin($currentUser(), $userId);
    $json($userService->getById($userId));
}, [$authMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/users/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use (
        $id,
        $currentUser,
        $readBody,
        $userService,
        $json,
        $isOwnerOrAdmin,
        $method
    ): void {
        $userId = $id($rawId);
        $user = $currentUser();
        $isOwnerOrAdmin($user, $userId);
        $json($userService->update(
            $userId,
            $readBody(),
            $user['role'] === 'admin',
            (int) $user['id'],
            $method === 'PUT'
        ));
    }, [$authMiddleware]);
}
$route('DELETE', '/api/users/@id:[A-Za-z0-9._~-]+', static function (string $rawId) use ($id, $userService, $json): void {
    $userService->delete($id($rawId));
    $json(['message' => 'User deleted successfully.']);
}, [$authMiddleware, $adminMiddleware]);

$openApiHandler = static function (): void {
    $specification = \OpenApi\Generator::scan([__DIR__ . '/openapi']);
    if ($specification === null) {
        throw new RuntimeException('The OpenAPI specification could not be generated.');
    }

    Flight::response()->header('Content-Type', 'application/json; charset=utf-8');
    Flight::response()->write($specification->toJson());
};
$route('GET', '/api/docs/openapi.json', $openApiHandler);
if (PHP_SAPI === 'cli-server') {
    $route('GET', '/openapi.json', $openApiHandler);
}

$route('GET', '/api/docs', static function (): void {
    $html = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sarajevo2B API - Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function () {
            window.ui = SwaggerUIBundle({
                url: '/api/docs/openapi.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [SwaggerUIBundle.presets.apis],
                layout: 'BaseLayout'
            });
        };
    </script>
</body>
</html>
HTML;

    Flight::response()->header('Content-Type', 'text/html; charset=utf-8');
    Flight::response()->write($html);
});

Flight::start();
