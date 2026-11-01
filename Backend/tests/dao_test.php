<?php

use Sarajevo2B\Backend\DAO\CategoryDAO;
use Sarajevo2B\Backend\DAO\EventDAO;
use Sarajevo2B\Backend\DAO\PlaceDAO;
use Sarajevo2B\Backend\DAO\ReviewDAO;
use Sarajevo2B\Backend\DAO\UserDAO;
use Sarajevo2B\Backend\Services\AuthService;
use Sarajevo2B\Backend\Services\CategoryService;
use Sarajevo2B\Backend\Services\EventService;
use Sarajevo2B\Backend\Services\PlaceService;
use Sarajevo2B\Backend\Services\ReviewService;
use Sarajevo2B\Backend\Services\UserService;

require __DIR__ . '/../vendor/autoload.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec(
    'CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(10) NOT NULL DEFAULT "user" CHECK (role IN ("admin", "user")),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$pdo->exec(
    'CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$pdo->exec(
    'CREATE TABLE places (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        address VARCHAR(255) NOT NULL,
        latitude NUMERIC,
        longitude NUMERIC,
        phone VARCHAR(30),
        website VARCHAR(255),
        opening_hours TEXT,
        image_url VARCHAR(500),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$pdo->exec(
    'CREATE TABLE events (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        place_id INTEGER REFERENCES places(id) ON DELETE SET NULL,
        title VARCHAR(150) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        start_time TIME,
        end_time TIME,
        image_url VARCHAR(500),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )'
);
$pdo->exec(
    'CREATE TABLE reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        place_id INTEGER NOT NULL REFERENCES places(id) ON DELETE CASCADE,
        rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
        comment TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (user_id, place_id)
    )'
);

$users = new UserDAO($pdo);
$categories = new CategoryDAO($pdo);
$places = new PlaceDAO($pdo);
$events = new EventDAO($pdo);
$reviews = new ReviewDAO($pdo);
$userService = new UserService($users);
$categoryService = new CategoryService($categories);
$placeService = new PlaceService($places);
$eventService = new EventService($events);
$reviewService = new ReviewService($reviews);

$user = $userService->register([
    'name' => 'DAO Test User',
    'email' => 'dao-' . bin2hex(random_bytes(6)) . '@example.test',
    'password' => 'TestPassword123',
]);
$userId = (int) $user['id'];
assertTrue(!array_key_exists('password_hash', $user), 'User API data must not expose password_hash.');
$storedUser = $users->getByEmail($user['email']);
assertTrue($storedUser !== null && password_verify('TestPassword123', $storedUser['password_hash']), 'Passwords must be hashed.');
$duplicateEmailRejected = false;
try {
    $userService->register([
        'name' => 'Duplicate User',
        'email' => $user['email'],
        'password' => 'AnotherPassword123',
    ]);
} catch (PDOException $exception) {
    $duplicateEmailRejected = (string) $exception->getCode() === '23000';
}
assertTrue($duplicateEmailRejected, 'Duplicate email was accepted.');
$roleMassAssignmentRejected = false;
try {
    $userService->register([
        'name' => 'Injected Admin',
        'email' => 'injected-' . bin2hex(random_bytes(6)) . '@example.test',
        'password' => 'AnotherPassword123',
        'role' => 'admin',
    ]);
} catch (\Sarajevo2B\Backend\Services\ApiException $exception) {
    $roleMassAssignmentRejected = $exception->getStatus() === 422;
}
assertTrue($roleMassAssignmentRejected, 'Public registration accepted a role field.');
$user = $users->update($userId, ['name' => 'Updated Test User']);
assertTrue($user['name'] === 'Updated Test User', 'User update failed.');
assertTrue(count($users->getAll()) === 1 && $users->getById($userId) !== null, 'User retrieval failed.');
$updatedProfile = $userService->update($userId, ['password' => 'ChangedPassword123'], false);
$storedUser = $users->getByEmail($user['email']);
assertTrue(
    !array_key_exists('password_hash', $updatedProfile)
        && $storedUser !== null
        && password_verify('ChangedPassword123', $storedUser['password_hash']),
    'Password update must hash the password and omit the hash from API data.'
);
$authService = new AuthService($users, str_repeat('s', 32), 3600);
$login = $authService->login(['email' => $user['email'], 'password' => 'ChangedPassword123']);
assertTrue(!array_key_exists('password_hash', $login['user']), 'Login response exposed password_hash.');
assertTrue($authService->authenticate($login['token'])['role'] === 'user', 'JWT authentication failed.');
$invalidTokenRejected = false;
try {
    $authService->authenticate('invalid.jwt.token');
} catch (\Sarajevo2B\Backend\Services\ApiException $exception) {
    $invalidTokenRejected = $exception->getStatus() === 401;
}
assertTrue($invalidTokenRejected, 'Invalid JWT was accepted.');
$users->update($userId, ['role' => 'admin']);
assertTrue($authService->authenticate($login['token'])['role'] === 'admin', 'JWT authentication trusted a stale token role.');
$users->update($userId, ['role' => 'user']);

$category = $categoryService->create(['name' => "'; SELECT 1; --", 'description' => 'Prepared value']);
$categoryId = (int) $category['id'];
assertTrue($categoryService->getById($categoryId)['name'] === "'; SELECT 1; --", 'Parameterized string value was not preserved.');
$category = $categoryService->update($categoryId, ['description' => 'Partial update']);
assertTrue($category['description'] === 'Partial update', 'Category partial update failed.');
assertTrue(count($categoryService->getAll()) === 1, 'Category retrieval failed.');

$place = $placeService->create([
    'category_id' => $categoryId,
    'name' => 'DAO Test Place',
    'address' => 'Sarajevo',
    'latitude' => 43.8563,
    'longitude' => 18.4131,
]);
$placeId = (int) $place['id'];
$place = $placeService->update($placeId, ['phone' => '+387000000']);
assertTrue($place['phone'] === '+387000000', 'Place partial update failed.');
assertTrue(count($placeService->getAll()) === 1 && $placeService->getById($placeId)['id'] == $placeId, 'Place retrieval failed.');

$event = $eventService->create([
    'place_id' => $placeId,
    'title' => 'DAO Test Event',
    'event_date' => '2027-05-15',
]);
$eventId = (int) $event['id'];
$event = $eventService->update($eventId, ['title' => 'Updated Test Event']);
assertTrue($event['title'] === 'Updated Test Event', 'Event partial update failed.');
assertTrue(count($eventService->getAll()) === 1 && $eventService->getById($eventId) !== [], 'Event retrieval failed.');
$event = $eventService->update($eventId, ['place_id' => null]);
assertTrue($event['place_id'] === null, 'Nullable event place reference was not accepted.');

$review = $reviewService->create($userId, ['place_id' => $placeId, 'rating' => 5, 'comment' => 'Good']);
$reviewId = (int) $review['id'];
$review = $reviewService->update($reviewId, ['rating' => 4]);
assertTrue((int) $review['rating'] === 4, 'Review partial update failed.');
assertTrue(count($reviewService->getAll()) === 1 && $reviewService->getById($reviewId) !== [], 'Review retrieval failed.');

$invalidRatingRejected = false;
try {
    $reviewService->update($reviewId, ['rating' => 6]);
} catch (\Sarajevo2B\Backend\Services\ApiException $exception) {
    $invalidRatingRejected = $exception->getStatus() === 422;
}
assertTrue($invalidRatingRejected, 'Out-of-range review rating was accepted.');

$foreignKeyRejected = false;
try {
    $placeService->create(['category_id' => 99999, 'name' => 'Invalid FK', 'address' => 'Sarajevo']);
} catch (PDOException $exception) {
    $foreignKeyRejected = true;
}
assertTrue($foreignKeyRejected, 'Invalid category foreign key was accepted.');

$invalidColumnRejected = false;
try {
    $categories->create(['name' => 'Unexpected', 'created_at' => 'injected']);
} catch (InvalidArgumentException $exception) {
    $invalidColumnRejected = true;
}
assertTrue($invalidColumnRejected, 'A non-permitted DAO column was accepted.');

foreach ([
    [$reviews, $reviewId],
    [$events, $eventId],
    [$places, $placeId],
    [$categories, $categoryId],
    [$users, $userId],
] as [$dao, $recordId]) {
    assertTrue($dao->delete($recordId), 'DAO delete failed.');
    assertTrue($dao->getById($recordId) === null, 'Deleted record is still present.');
}

$exampleConfig = require __DIR__ . '/../config/config.example.php';
$localConfig = require __DIR__ . '/../config/config.php';
assertTrue(
    isset($exampleConfig['database']['name'], $exampleConfig['jwt']['secret'])
        && isset($localConfig['database']['name'], $localConfig['jwt']['secret']),
    'Database configuration did not load expected values.'
);

echo "DAO tests passed for users, categories, places, events, and reviews.\n";
