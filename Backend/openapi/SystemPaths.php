<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/health',
    tags: ['System'],
    summary: 'Check API health',
    responses: [
        new OA\Response(
            response: 200,
            description: 'API is ready.',
            content: new OA\JsonContent(ref: '#/components/schemas/HealthResponse')
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/docs/openapi.json',
    tags: ['System'],
    summary: 'Get the OpenAPI specification',
    description: 'Public, generated from the API attribute definitions. This response is the raw OpenAPI document rather than the standard data envelope.',
    responses: [
        new OA\Response(
            response: 200,
            description: 'OpenAPI 3.0 specification.',
            content: [new OA\MediaType(mediaType: 'application/json')]
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
#[OA\Get(
    path: '/docs',
    tags: ['System'],
    summary: 'Open Swagger UI',
    description: 'Public Swagger UI page that loads /api/docs/openapi.json and supports JWT bearer authorization.',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Swagger UI HTML page.',
            content: [new OA\MediaType(mediaType: 'text/html', schema: new OA\Schema(type: 'string'))]
        ),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
    ]
)]
final class SystemPaths
{
}
