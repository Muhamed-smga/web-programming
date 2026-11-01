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
use Sarajevo2B\Backend\Services\PlaceService;
use Sarajevo2B\Backend\Services\ReviewService;
use Sarajevo2B\Backend\Services\UserService;

require __DIR__ . '/vendor/autoload.php';

Flight::map('error', static function (Throwable $exception): void {
    if ($exception instanceof ApiException) {
        Flight::json(['error' => $exception->getMessage()], $exception->getStatus());
        return;
    }

    if ($exception instanceof PDOException && $exception->getCode() === '23000') {
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        if ($driverCode === 1062) {
            Flight::json(['error' => 'A record with the same unique value already exists.'], 409);
            return;
        }
        if ($driverCode === 1452) {
            Flight::json(['error' => 'A referenced record does not exist.'], 422);
            return;
        }
        if ($driverCode === 1451) {
            Flight::json(['error' => 'This record is still referenced and cannot be deleted.'], 409);
            return;
        }
    }

    if ($exception instanceof \InvalidArgumentException) {
        Flight::json(['error' => $exception->getMessage()], 422);
        return;
    }

    error_log((string) $exception);
    Flight::json(['error' => 'An unexpected server error occurred.'], 500);
});

Flight::map('notFound', static function (): void {
    Flight::json(['error' => 'Endpoint not found.'], 404);
});

try {
    $config = require __DIR__ . '/config/config.php';
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
$placeService = new PlaceService(new PlaceDAO($pdo));
$eventService = new EventService(new EventDAO($pdo));
$reviewService = new ReviewService(new ReviewDAO($pdo));

$readBody = static function (): array {
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

$noContent = static function (): void {
    Flight::response()->status(204);
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
    $json($userService->update((int) $user['id'], $readBody(), false));
}, [$authMiddleware]);

$route('GET', '/api/categories', static function () use ($categoryService, $json): void {
    $json($categoryService->getAll());
});
$route('GET', '/api/categories/@id:[0-9]+', static function (string $rawId) use ($id, $categoryService, $json): void {
    $json($categoryService->getById($id($rawId)));
});
$route('POST', '/api/categories', static function () use ($readBody, $categoryService, $json): void {
    $json($categoryService->create($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/categories/@id:[0-9]+', static function (string $rawId) use (
        $id,
        $readBody,
        $categoryService,
        $json,
        $method
    ): void {
        $json($categoryService->update($id($rawId), $readBody(), $method === 'PUT'));
    }, [$authMiddleware, $adminMiddleware]);
}
$route('DELETE', '/api/categories/@id:[0-9]+', static function (string $rawId) use ($id, $categoryService, $noContent): void {
    $categoryService->delete($id($rawId));
    $noContent();
}, [$authMiddleware, $adminMiddleware]);

$route('GET', '/api/places', static function () use ($placeService, $json): void {
    $json($placeService->getAll());
});
$route('GET', '/api/places/@id:[0-9]+', static function (string $rawId) use ($id, $placeService, $json): void {
    $json($placeService->getById($id($rawId)));
});
$route('POST', '/api/places', static function () use ($readBody, $placeService, $json): void {
    $json($placeService->create($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/places/@id:[0-9]+', static function (string $rawId) use (
        $id,
        $readBody,
        $placeService,
        $json,
        $method
    ): void {
        $json($placeService->update($id($rawId), $readBody(), $method === 'PUT'));
    }, [$authMiddleware, $adminMiddleware]);
}
$route('DELETE', '/api/places/@id:[0-9]+', static function (string $rawId) use ($id, $placeService, $noContent): void {
    $placeService->delete($id($rawId));
    $noContent();
}, [$authMiddleware, $adminMiddleware]);

$route('GET', '/api/events', static function () use ($eventService, $json): void {
    $json($eventService->getAll());
});
$route('GET', '/api/events/@id:[0-9]+', static function (string $rawId) use ($id, $eventService, $json): void {
    $json($eventService->getById($id($rawId)));
});
$route('POST', '/api/events', static function () use ($readBody, $eventService, $json): void {
    $json($eventService->create($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
foreach (['PUT', 'PATCH'] as $method) {
    $route($method, '/api/events/@id:[0-9]+', static function (string $rawId) use (
        $id,
        $readBody,
        $eventService,
        $json,
        $method
    ): void {
        $json($eventService->update($id($rawId), $readBody(), $method === 'PUT'));
    }, [$authMiddleware, $adminMiddleware]);
}
$route('DELETE', '/api/events/@id:[0-9]+', static function (string $rawId) use ($id, $eventService, $noContent): void {
    $eventService->delete($id($rawId));
    $noContent();
}, [$authMiddleware, $adminMiddleware]);

$route('GET', '/api/reviews', static function () use ($reviewService, $json): void {
    $json($reviewService->getAll());
});
$route('GET', '/api/reviews/@id:[0-9]+', static function (string $rawId) use ($id, $reviewService, $json): void {
    $json($reviewService->getById($id($rawId)));
});
$route('POST', '/api/reviews', static function () use ($currentUser, $readBody, $reviewService, $json): void {
    $user = $currentUser();
    $json($reviewService->create((int) $user['id'], $readBody()), 201);
}, [$authMiddleware]);
$route('PUT', '/api/reviews/@id:[0-9]+', static function (string $rawId) use (
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
$route('PATCH', '/api/reviews/@id:[0-9]+', static function (string $rawId) use (
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
$route('DELETE', '/api/reviews/@id:[0-9]+', static function (string $rawId) use (
    $id,
    $currentUser,
    $reviewService,
    $isOwnerOrAdmin,
    $noContent
): void {
    $reviewId = $id($rawId);
    $user = $currentUser();
    $review = $reviewService->getById($reviewId);
    $isOwnerOrAdmin($user, (int) $review['user_id']);
    $reviewService->delete($reviewId);
    $noContent();
}, [$authMiddleware]);

$route('GET', '/api/users', static function () use ($userService, $json): void {
    $json($userService->getAll());
}, [$authMiddleware, $adminMiddleware]);
$route('POST', '/api/users', static function () use ($readBody, $userService, $json): void {
    $json($userService->createByAdmin($readBody()), 201);
}, [$authMiddleware, $adminMiddleware]);
$route('GET', '/api/users/@id:[0-9]+', static function (string $rawId) use (
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
    $route($method, '/api/users/@id:[0-9]+', static function (string $rawId) use (
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
        $json($userService->update($userId, $readBody(), $user['role'] === 'admin', $method === 'PUT'));
    }, [$authMiddleware]);
}
$route('DELETE', '/api/users/@id:[0-9]+', static function (string $rawId) use ($id, $userService, $noContent): void {
    $userService->delete($id($rawId));
    $noContent();
}, [$authMiddleware, $adminMiddleware]);

Flight::start();
