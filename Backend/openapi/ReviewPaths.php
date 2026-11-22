<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/reviews',
    tags: ['Reviews'],
    summary: 'List reviews',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Reviews returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/ReviewListResponse')
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/reviews/{id}',
    tags: ['Reviews'],
    summary: 'Get a review',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Review returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/ReviewResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Post(
    path: '/reviews',
    tags: ['Reviews'],
    summary: 'Create a review',
    description: 'Authentication is required. A user may create only one review per place, and place_id must reference an existing place.',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/ReviewCreateRequest',
            example: ['place_id' => 1, 'rating' => 5, 'comment' => 'Excellent coffee and friendly staff.']
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Review created.',
            content: new OA\JsonContent(ref: '#/components/schemas/ReviewResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Put(
    path: '/reviews/{id}',
    tags: ['Reviews'],
    summary: 'Replace a review',
    description: 'Only the review owner or an administrator may modify it. PUT requires both rating and comment; rating must be from 1 through 5.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/ReviewPutRequest', example: ['rating' => 4, 'comment' => 'Updated review text.'])
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Review updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/ReviewResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Patch(
    path: '/reviews/{id}',
    tags: ['Reviews'],
    summary: 'Partially update a review',
    description: 'Only the review owner or an administrator may modify it. PATCH accepts a non-empty subset of rating and comment.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/ReviewPatchRequest', example: ['rating' => 4])
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Review updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/ReviewResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Delete(
    path: '/reviews/{id}',
    tags: ['Reviews'],
    summary: 'Delete a review',
    description: 'Only the review owner or an administrator may delete it.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Review deleted.',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['data' => ['message' => 'Review deleted successfully.']])
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class ReviewPaths
{
}
