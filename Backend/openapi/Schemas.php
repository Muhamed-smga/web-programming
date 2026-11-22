<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    type: 'object',
    required: ['id', 'name', 'email', 'role', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Amina Hadzic'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'amina@example.com'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-15 10:30:00'),
    ]
)]
final class UserSchema
{
}

#[OA\Schema(
    schema: 'Category',
    type: 'object',
    required: ['id', 'name', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Coffee Bars'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Coffee shops and cafes in Sarajevo'),
        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-15 10:30:00'),
    ]
)]
final class CategorySchema
{
}

#[OA\Schema(
    schema: 'Place',
    type: 'object',
    required: ['id', 'category_id', 'name', 'address', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Demo Coffee Bar'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'A sample coffee bar in Sarajevo'),
        new OA\Property(property: 'address', type: 'string', maxLength: 255, example: 'Ferhadija, Sarajevo'),
        new OA\Property(property: 'latitude', type: 'number', format: 'double', nullable: true, minimum: -90, maximum: 90, example: 43.8582),
        new OA\Property(property: 'longitude', type: 'number', format: 'double', nullable: true, minimum: -180, maximum: 180, example: 18.4231),
        new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+387 33 123 456'),
        new OA\Property(property: 'website', type: 'string', maxLength: 255, nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'opening_hours', type: 'string', nullable: true, example: '09:00 - 22:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/place.jpg'),
        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-15 10:30:00'),
    ]
)]
final class PlaceSchema
{
}

#[OA\Schema(
    schema: 'Event',
    type: 'object',
    required: ['id', 'title', 'event_date', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'place_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Cultural Evening'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Live music and local food'),
        new OA\Property(property: 'event_date', type: 'string', format: 'date', example: '2027-05-20'),
        new OA\Property(property: 'start_time', type: 'string', format: 'time', nullable: true, example: '19:00:00'),
        new OA\Property(property: 'end_time', type: 'string', format: 'time', nullable: true, example: '21:00:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/event.jpg'),
        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-15 10:30:00'),
    ]
)]
final class EventSchema
{
}

#[OA\Schema(
    schema: 'Review',
    type: 'object',
    required: ['id', 'user_id', 'place_id', 'rating', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 2),
        new OA\Property(property: 'place_id', type: 'integer', example: 1),
        new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
        new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Excellent coffee and friendly staff.'),
        new OA\Property(property: 'created_at', type: 'string', example: '2025-01-15 10:30:00'),
    ]
)]
final class ReviewSchema
{
}

#[OA\Schema(
    schema: 'UserResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/User')],
    example: ['data' => ['id' => 1, 'name' => 'Amina Hadzic', 'email' => 'amina@example.com', 'role' => 'user', 'created_at' => '2025-01-15 10:30:00']]
)]
#[OA\Schema(
    schema: 'UserListResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User'))],
    example: ['data' => [['id' => 1, 'name' => 'Amina Hadzic', 'email' => 'amina@example.com', 'role' => 'user', 'created_at' => '2025-01-15 10:30:00']]]
)]
final class UserResponseSchemas
{
}

#[OA\Schema(
    schema: 'CategoryResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Category')],
    example: ['data' => ['id' => 1, 'name' => 'Coffee Bars', 'description' => 'Coffee shops and cafes in Sarajevo', 'created_at' => '2025-01-15 10:30:00']]
)]
#[OA\Schema(
    schema: 'CategoryListResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category'))],
    example: ['data' => [['id' => 1, 'name' => 'Coffee Bars', 'description' => 'Coffee shops and cafes in Sarajevo', 'created_at' => '2025-01-15 10:30:00']]]
)]
final class CategoryResponseSchemas
{
}

#[OA\Schema(
    schema: 'PlaceResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Place')],
    example: ['data' => ['id' => 1, 'category_id' => 1, 'name' => 'Demo Coffee Bar', 'description' => 'A sample coffee bar in Sarajevo', 'address' => 'Ferhadija, Sarajevo', 'latitude' => 43.8582, 'longitude' => 18.4231, 'phone' => null, 'website' => null, 'opening_hours' => '09:00 - 22:00', 'image_url' => null, 'created_at' => '2025-01-15 10:30:00']]
)]
#[OA\Schema(
    schema: 'PlaceListResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Place'))],
    example: ['data' => [['id' => 1, 'category_id' => 1, 'name' => 'Demo Coffee Bar', 'description' => 'A sample coffee bar in Sarajevo', 'address' => 'Ferhadija, Sarajevo', 'latitude' => 43.8582, 'longitude' => 18.4231, 'phone' => null, 'website' => null, 'opening_hours' => '09:00 - 22:00', 'image_url' => null, 'created_at' => '2025-01-15 10:30:00']]]
)]
final class PlaceResponseSchemas
{
}

#[OA\Schema(
    schema: 'EventResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Event')],
    example: ['data' => ['id' => 1, 'place_id' => 1, 'title' => 'Cultural Evening', 'description' => 'Live music and local food', 'event_date' => '2027-05-20', 'start_time' => '19:00:00', 'end_time' => '21:00:00', 'image_url' => null, 'created_at' => '2025-01-15 10:30:00']]
)]
#[OA\Schema(
    schema: 'EventListResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Event'))],
    example: ['data' => [['id' => 1, 'place_id' => 1, 'title' => 'Cultural Evening', 'description' => 'Live music and local food', 'event_date' => '2027-05-20', 'start_time' => '19:00:00', 'end_time' => '21:00:00', 'image_url' => null, 'created_at' => '2025-01-15 10:30:00']]]
)]
final class EventResponseSchemas
{
}

#[OA\Schema(
    schema: 'ReviewResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Review')],
    example: ['data' => ['id' => 1, 'user_id' => 2, 'place_id' => 1, 'rating' => 5, 'comment' => 'Excellent coffee and friendly staff.', 'created_at' => '2025-01-15 10:30:00']]
)]
#[OA\Schema(
    schema: 'ReviewListResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review'))],
    example: ['data' => [['id' => 1, 'user_id' => 2, 'place_id' => 1, 'rating' => 5, 'comment' => 'Excellent coffee and friendly staff.', 'created_at' => '2025-01-15 10:30:00']]]
)]
final class ReviewResponseSchemas
{
}

#[OA\Schema(
    schema: 'WeatherResponse',
    type: 'object',
    required: ['data'],
    properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Weather')],
    example: ['data' => ['location' => 'Sarajevo', 'temperature' => 18.5, 'feels_like' => 18.1, 'humidity' => 62, 'description' => 'scattered clouds', 'icon' => '03d', 'wind_speed' => 3.1]]
)]
#[OA\Schema(
    schema: 'HealthResponse',
    type: 'object',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            required: ['status'],
            properties: [new OA\Property(property: 'status', type: 'string', example: 'ok')]
        ),
    ],
    example: ['data' => ['status' => 'ok']]
)]
final class SystemResponseSchemas
{
}

#[OA\Schema(
    schema: 'CategoryCreateRequest',
    type: 'object',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Coffee Bars'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Coffee shops and cafes in Sarajevo'),
    ]
)]
#[OA\Schema(
    schema: 'CategoryPutRequest',
    type: 'object',
    required: ['name', 'description'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Coffee Bars'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Coffee shops and cafes in Sarajevo'),
    ]
)]
#[OA\Schema(
    schema: 'CategoryPatchRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Coffee Bars'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Coffee shops and cafes in Sarajevo'),
    ]
)]
final class CategoryRequests
{
}

#[OA\Schema(
    schema: 'PlaceCreateRequest',
    type: 'object',
    required: ['category_id', 'name', 'address'],
    properties: [
        new OA\Property(property: 'category_id', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Demo Coffee Bar'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'A sample coffee bar in Sarajevo'),
        new OA\Property(property: 'address', type: 'string', maxLength: 255, example: 'Ferhadija, Sarajevo'),
        new OA\Property(property: 'latitude', type: 'number', format: 'double', nullable: true, minimum: -90, maximum: 90, example: 43.8582),
        new OA\Property(property: 'longitude', type: 'number', format: 'double', nullable: true, minimum: -180, maximum: 180, example: 18.4231),
        new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+387 33 123 456'),
        new OA\Property(property: 'website', type: 'string', maxLength: 255, nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'opening_hours', type: 'string', nullable: true, example: '09:00 - 22:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/place.jpg'),
    ]
)]
#[OA\Schema(
    schema: 'PlacePutRequest',
    type: 'object',
    required: ['category_id', 'name', 'description', 'address', 'latitude', 'longitude', 'phone', 'website', 'opening_hours', 'image_url'],
    properties: [
        new OA\Property(property: 'category_id', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Demo Coffee Bar'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'A sample coffee bar in Sarajevo'),
        new OA\Property(property: 'address', type: 'string', maxLength: 255, example: 'Ferhadija, Sarajevo'),
        new OA\Property(property: 'latitude', type: 'number', format: 'double', nullable: true, minimum: -90, maximum: 90, example: 43.8582),
        new OA\Property(property: 'longitude', type: 'number', format: 'double', nullable: true, minimum: -180, maximum: 180, example: 18.4231),
        new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+387 33 123 456'),
        new OA\Property(property: 'website', type: 'string', maxLength: 255, nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'opening_hours', type: 'string', nullable: true, example: '09:00 - 22:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/place.jpg'),
    ]
)]
#[OA\Schema(
    schema: 'PlacePatchRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'category_id', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'name', type: 'string', maxLength: 150, example: 'Demo Coffee Bar'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'A sample coffee bar in Sarajevo'),
        new OA\Property(property: 'address', type: 'string', maxLength: 255, example: 'Ferhadija, Sarajevo'),
        new OA\Property(property: 'latitude', type: 'number', format: 'double', nullable: true, minimum: -90, maximum: 90, example: 43.8582),
        new OA\Property(property: 'longitude', type: 'number', format: 'double', nullable: true, minimum: -180, maximum: 180, example: 18.4231),
        new OA\Property(property: 'phone', type: 'string', maxLength: 30, nullable: true, example: '+387 33 123 456'),
        new OA\Property(property: 'website', type: 'string', maxLength: 255, nullable: true, example: 'https://example.com'),
        new OA\Property(property: 'opening_hours', type: 'string', nullable: true, example: '09:00 - 22:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/place.jpg'),
    ]
)]
final class PlaceRequests
{
}

#[OA\Schema(
    schema: 'EventCreateRequest',
    type: 'object',
    required: ['title', 'event_date'],
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', nullable: true, minimum: 1, example: 1),
        new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Cultural Evening'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Live music and local food'),
        new OA\Property(property: 'event_date', type: 'string', format: 'date', example: '2027-05-20'),
        new OA\Property(property: 'start_time', type: 'string', format: 'time', nullable: true, example: '19:00:00'),
        new OA\Property(property: 'end_time', type: 'string', format: 'time', nullable: true, example: '21:00:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/event.jpg'),
    ]
)]
#[OA\Schema(
    schema: 'EventPutRequest',
    type: 'object',
    required: ['place_id', 'title', 'description', 'event_date', 'start_time', 'end_time', 'image_url'],
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', nullable: true, minimum: 1, example: 1),
        new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Cultural Evening'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Live music and local food'),
        new OA\Property(property: 'event_date', type: 'string', format: 'date', example: '2027-05-20'),
        new OA\Property(property: 'start_time', type: 'string', format: 'time', nullable: true, example: '19:00:00'),
        new OA\Property(property: 'end_time', type: 'string', format: 'time', nullable: true, example: '21:00:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/event.jpg'),
    ]
)]
#[OA\Schema(
    schema: 'EventPatchRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', nullable: true, minimum: 1, example: 1),
        new OA\Property(property: 'title', type: 'string', maxLength: 150, example: 'Cultural Evening'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Live music and local food'),
        new OA\Property(property: 'event_date', type: 'string', format: 'date', example: '2027-05-20'),
        new OA\Property(property: 'start_time', type: 'string', format: 'time', nullable: true, example: '19:00:00'),
        new OA\Property(property: 'end_time', type: 'string', format: 'time', nullable: true, example: '21:00:00'),
        new OA\Property(property: 'image_url', type: 'string', maxLength: 500, nullable: true, example: 'https://example.com/event.jpg'),
    ]
)]
final class EventRequests
{
}

#[OA\Schema(
    schema: 'ReviewCreateRequest',
    type: 'object',
    required: ['place_id', 'rating'],
    properties: [
        new OA\Property(property: 'place_id', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
        new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Excellent coffee and friendly staff.'),
    ]
)]
#[OA\Schema(
    schema: 'ReviewPutRequest',
    type: 'object',
    required: ['rating', 'comment'],
    properties: [
        new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
        new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Excellent coffee and friendly staff.'),
    ]
)]
#[OA\Schema(
    schema: 'ReviewPatchRequest',
    type: 'object',
    properties: [
        new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
        new OA\Property(property: 'comment', type: 'string', nullable: true, example: 'Excellent coffee and friendly staff.'),
    ]
)]
final class ReviewRequests
{
}

#[OA\Schema(
    schema: 'LoginRequest',
    type: 'object',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'admin@sarajevo2b.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 255, example: 'Admin123!'),
    ]
)]
final class LoginRequestSchema
{
}

#[OA\Schema(
    schema: 'RegisterRequest',
    type: 'object',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Amina Hadzic'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'amina@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 255, example: 'Secure123!'),
    ]
)]
final class RegisterRequestSchema
{
}

#[OA\Schema(
    schema: 'UserCreateRequest',
    type: 'object',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Amina Hadzic'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'amina@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 255, example: 'Secure123!'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
    ]
)]
#[OA\Schema(
    schema: 'UserPutRequest',
    type: 'object',
    required: ['name', 'email'],
    description: 'PUT requires name and email for owners; admins must also provide role. Password is optional. Regular users cannot change roles, and admins cannot change their own role. The service does not prevent demoting the last other admin.',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Amina Hadzic'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'amina@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 255, example: 'Secure123!'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
    ]
)]
#[OA\Schema(
    schema: 'UserPatchRequest',
    type: 'object',
    description: 'PATCH accepts any non-empty subset. Regular users cannot change roles, and admins cannot change their own role. The service does not prevent demoting the last other admin.',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100, example: 'Amina Hadzic'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 150, example: 'amina@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, maxLength: 255, example: 'Secure123!'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
    ]
)]
final class UserRequests
{
}

#[OA\Schema(
    schema: 'LoginResponse',
    type: 'object',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            required: ['token', 'user'],
            properties: [
                new OA\Property(property: 'token', type: 'string', example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'),
                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
            ]
        ),
    ],
    example: [
        'data' => [
            'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
            'user' => ['id' => 1, 'name' => 'Admin', 'email' => 'admin@sarajevo2b.com', 'role' => 'admin', 'created_at' => '2025-01-15 10:30:00'],
        ],
    ]
)]
final class LoginResponseSchema
{
}

#[OA\Schema(
    schema: 'Weather',
    type: 'object',
    required: ['location', 'temperature', 'feels_like', 'humidity', 'description', 'icon', 'wind_speed'],
    properties: [
        new OA\Property(property: 'location', type: 'string', example: 'Sarajevo'),
        new OA\Property(property: 'temperature', type: 'number', format: 'float', example: 18.5),
        new OA\Property(property: 'feels_like', type: 'number', format: 'float', example: 18.1),
        new OA\Property(property: 'humidity', type: 'integer', example: 62),
        new OA\Property(property: 'description', type: 'string', example: 'scattered clouds'),
        new OA\Property(property: 'icon', type: 'string', example: '03d'),
        new OA\Property(property: 'wind_speed', type: 'number', format: 'float', example: 3.1),
    ]
)]
final class WeatherSchema
{
}

#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    required: ['error'],
    properties: [
        new OA\Property(property: 'error', type: 'string', example: 'The request is invalid.'),
    ],
    example: ['error' => 'The request is invalid.']
)]
#[OA\Schema(
    schema: 'MessageResponse',
    type: 'object',
    required: ['data'],
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            required: ['message'],
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Category deleted successfully.'),
            ]
        ),
    ],
    example: ['data' => ['message' => 'Category deleted successfully.']]
)]
final class CommonSchemas
{
}
