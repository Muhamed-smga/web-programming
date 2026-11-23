<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/categories',
    tags: ['Categories'],
    summary: 'List categories',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Categories returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryListResponse')
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/categories/{id}',
    tags: ['Categories'],
    summary: 'Get a category',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Category returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Post(
    path: '/categories',
    tags: ['Categories'],
    summary: 'Create a category',
    description: 'Administrator-only.',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CategoryCreateRequest', example: ['name' => 'Coffee Bars', 'description' => 'Coffee shops and cafes in Sarajevo'])
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Category created.',
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Put(
    path: '/categories/{id}',
    tags: ['Categories'],
    summary: 'Replace a category',
    description: 'Administrator-only. PUT requires both name and description. Send null for a description with no value.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CategoryPutRequest', example: ['name' => 'Coffee Bars', 'description' => 'Coffee shops in Sarajevo'])
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Category updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Patch(
    path: '/categories/{id}',
    tags: ['Categories'],
    summary: 'Partially update a category',
    description: 'Administrator-only. PATCH accepts a non-empty subset of name and description.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CategoryPatchRequest', example: ['description' => 'Coffee shops and cafes'])
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Category updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Delete(
    path: '/categories/{id}',
    tags: ['Categories'],
    summary: 'Delete a category',
    description: 'Administrator-only. A category cannot be deleted while places still use it.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Category deleted.',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['data' => ['message' => 'Category deleted successfully.']])
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class CategoryPaths
{
}
