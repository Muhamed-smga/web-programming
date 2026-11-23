<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/me',
    tags: ['Me'],
    summary: 'Get the authenticated profile',
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Current user profile.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Patch(
    path: '/me',
    tags: ['Me'],
    summary: 'Update the authenticated profile',
    description: 'PATCH accepts a non-empty subset of name, email, and password. A user cannot change their own role.',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UserPatchRequest',
            example: ['name' => 'Muhamed Smailbegovic']
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Profile updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/users',
    tags: ['Users'],
    summary: 'List users',
    description: 'Administrator-only user listing. Password hashes are never returned.',
    security: [['bearerAuth' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Users returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserListResponse')
        ),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Post(
    path: '/users',
    tags: ['Users'],
    summary: 'Create a user',
    description: 'Administrator-only. Email must be unique; the optional role is admin or user.',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UserCreateRequest',
            example: ['name' => 'Amina Hadzic', 'email' => 'amina@example.com', 'password' => 'Secure123!', 'role' => 'user']
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'User created.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/users/{id}',
    tags: ['Users'],
    summary: 'Get a user',
    description: 'The user may view their own record; administrators may view any record.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User returned without password hash.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Put(
    path: '/users/{id}',
    tags: ['Users'],
    summary: 'Replace user fields',
    description: 'The owner or an administrator may update the user. PUT requires name and email; administrators must also send role. Password is optional. Regular users cannot change roles, and administrators cannot change their own role. The service prevents deleting the last administrator but does not prevent demoting the last other administrator.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UserPutRequest',
            example: ['name' => 'Muhamed Smailbegovic', 'email' => 'user@sarajevo2b.com', 'role' => 'user']
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'User updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Patch(
    path: '/users/{id}',
    tags: ['Users'],
    summary: 'Partially update a user',
    description: 'The owner or an administrator may update a non-empty subset of user fields. Regular users cannot change roles, and administrators cannot change their own role. The service prevents deleting the last administrator but does not prevent demoting the last other administrator.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 2)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/UserPatchRequest',
            example: ['name' => 'Muhamed Smailbegovic']
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'User updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Delete(
    path: '/users/{id}',
    tags: ['Users'],
    summary: 'Delete a user',
    description: 'Administrator-only. The last administrator cannot be deleted.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 12)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User deleted.',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['data' => ['message' => 'User deleted successfully.']])
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class UserPaths
{
}
