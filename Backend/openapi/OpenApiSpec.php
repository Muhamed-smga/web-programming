<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(openapi: '3.0.0')]
#[OA\Info(
    title: 'Sarajevo2B API',
    version: '1.0.0',
    description: 'REST API for Sarajevo places, events, categories, reviews, and user accounts.'
)]
#[OA\Server(url: 'http://127.0.0.1:8000/api', description: 'Local development server')]
#[OA\Tag(name: 'System', description: 'Health and API documentation')]
#[OA\Tag(name: 'Auth', description: 'Registration and login')]
#[OA\Tag(name: 'Me', description: 'The authenticated user profile')]
#[OA\Tag(name: 'Users', description: 'User accounts')]
#[OA\Tag(name: 'Categories', description: 'Place categories')]
#[OA\Tag(name: 'Places', description: 'Places in Sarajevo')]
#[OA\Tag(name: 'Events', description: 'Events at places')]
#[OA\Tag(name: 'Reviews', description: 'User reviews of places')]
#[OA\Tag(name: 'External', description: 'Third-party weather data')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
final class OpenApiSpec
{
}
