<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/events',
    tags: ['Events'],
    summary: 'List events',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Events returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/EventListResponse')
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/events/{id}',
    tags: ['Events'],
    summary: 'Get an event',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Event returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/EventResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Post(
    path: '/events',
    tags: ['Events'],
    summary: 'Create an event',
    description: 'Administrator-only. place_id is nullable but must reference an existing place when supplied. If both times are supplied, end_time must be after start_time.',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/EventCreateRequest',
            example: ['place_id' => 1, 'title' => 'Cultural Evening', 'description' => 'Live music and local food', 'event_date' => '2027-05-20', 'start_time' => '19:00:00', 'end_time' => '21:00:00']
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Event created.',
            content: new OA\JsonContent(ref: '#/components/schemas/EventResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Put(
    path: '/events/{id}',
    tags: ['Events'],
    summary: 'Replace an event',
    description: 'Administrator-only. PUT requires every event field. Nullable fields must be included with null when empty. If both times are supplied, end_time must be after start_time.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/EventPutRequest',
            example: ['place_id' => 1, 'title' => 'Cultural Evening', 'description' => 'Live music and local food', 'event_date' => '2027-05-20', 'start_time' => '19:00:00', 'end_time' => '21:00:00', 'image_url' => null]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Event updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/EventResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Patch(
    path: '/events/{id}',
    tags: ['Events'],
    summary: 'Partially update an event',
    description: 'Administrator-only. PATCH accepts a non-empty subset of event fields. The final start_time/end_time pair must have end_time after start_time.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/EventPatchRequest', example: ['end_time' => '22:00:00'])
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Event updated.',
            content: new OA\JsonContent(ref: '#/components/schemas/EventResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Delete(
    path: '/events/{id}',
    tags: ['Events'],
    summary: 'Delete an event',
    description: 'Administrator-only.',
    security: [['bearerAuth' => []]],
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1), example: 1)],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Event deleted.',
            content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse', example: ['data' => ['message' => 'Event deleted successfully.']])
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        new OA\Response(response: 403, ref: '#/components/responses/Forbidden'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class EventPaths
{
}
