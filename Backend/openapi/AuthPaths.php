<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/auth/register',
    tags: ['Auth'],
    summary: 'Register a regular user',
    description: 'Public registration always creates a regular user. Email addresses must be unique.',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/RegisterRequest',
            example: ['name' => 'Amina Hadzic', 'email' => 'amina@example.com', 'password' => 'Secure123!']
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'User registered.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Post(
    path: '/auth/login',
    tags: ['Auth'],
    summary: 'Log in',
    description: 'Returns a JWT and public user details. Incorrect credentials return 401.',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/LoginRequest',
            example: ['email' => 'admin@sarajevo2b.com', 'password' => 'Admin123!']
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Login successful.',
            content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class AuthPaths
{
}
