<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/external/weather',
    tags: ['External'],
    summary: 'Get current weather',
    description: 'Returns current weather for a place_id, a lat/lon pair, or Sarajevo by default (43.8563, 18.4131). Use either place_id or both coordinates, not both forms. Provider non-2xx or invalid responses return 502; network failures and timeouts return 504. An unconfigured API key returns 500.',
    parameters: [
        new OA\Parameter(name: 'place_id', in: 'query', required: false, description: 'Positive ID of a place with stored coordinates.', schema: new OA\Schema(type: 'integer', minimum: 1), example: 1),
        new OA\Parameter(name: 'lat', in: 'query', required: false, description: 'Latitude from -90 through 90; requires lon.', schema: new OA\Schema(type: 'number', format: 'double', minimum: -90, maximum: 90), example: 43.8563),
        new OA\Parameter(name: 'lon', in: 'query', required: false, description: 'Longitude from -180 through 180; requires lat.', schema: new OA\Schema(type: 'number', format: 'double', minimum: -180, maximum: 180), example: 18.4131),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Current weather returned.',
            content: new OA\JsonContent(ref: '#/components/schemas/WeatherResponse')
        ),
        new OA\Response(response: 400, ref: '#/components/responses/BadRequest'),
        new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        new OA\Response(response: 500, ref: '#/components/responses/InternalServerError'),
        new OA\Response(response: 502, ref: '#/components/responses/BadGateway'),
        new OA\Response(response: 504, ref: '#/components/responses/GatewayTimeout'),
    ]
)]
final class ExternalPaths
{
}
