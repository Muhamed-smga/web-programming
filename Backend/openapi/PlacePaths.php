<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/places',
    tags: ['Places'],
    summary: 'List places',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Places returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/PlaceListResponse')
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/places/{id}',
    tags: ['Places'],
    summary: 'Get a place',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Place returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/PlaceResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Post(
    path: '/places',
    tags: ['Places'],
    summary: 'Create a place',
    description: 'Administrator-only. category_id must reference an existing category; coordinates, if supplied, must be within latitude and longitude ranges.',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/PlaceCreateRequest',
            example: ['category_id' => 1, 'name' => 'Demo Coffee Bar', 'description' => 'A sample coffee bar', 'address' => 'Ferhadija, Sarajevo', 'latitude' => 43.8582, 'longitude' => 18.4231]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Place created.',
            content: new OA\JsonContent(ref: '#/components/schemas/PlaceResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Put(
    path: '/places/{id}',
    tags: ['Places'],
    summary: 'Replace a place',
    description: 'Administrator-only. PUT requires all place fields. Nullable fields must be included with null when empty.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/PlacePutRequest',
            example: ['category_id' => 1, 'name' => 'Demo Coffee Bar', 'description' => 'A sample coffee bar', 'address' => 'Ferhadija, Sarajevo', 'latitude' => 43.8582, 'longitude' => 18.4231, 'phone' => null, 'website' => null, 'opening_hours' => '09:00 - 22:00', 'image_url' => null]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Place updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/PlaceResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Patch(
    path: '/places/{id}',
    tags: ['Places'],
    summary: 'Partially update a place',
    description: 'Administrator-only. PATCH accepts a non-empty subset of place fields.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/PlacePatchRequest', example: ['opening_hours' => '10:00 - 20:00'])
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Place updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/PlaceResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Delete(
    path: '/places/{id}',
    tags: ['Places'],
    summary: 'Delete a place',
    description: 'Administrator-only.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Place deleted.',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['data' => ['message' => 'Place deleted successfully.']])
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class PlacePaths
{
}
