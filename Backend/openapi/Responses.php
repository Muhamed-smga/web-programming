<?php

namespace Sarajevo2B\Backend\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Components(
    responses: [
        new OA\Response(
            response: 'BadRequest',
            description: 'The request body, identifier, or business rule is invalid.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 'Unauthorized',
            description: 'Authentication is missing, invalid, or expired.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 'Forbidden',
            description: 'The authenticated user is not permitted to perform this operation.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 'NotFound',
            description: 'The requested record or endpoint does not exist.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 'InternalServerError',
            description: 'An unexpected server or upstream service error occurred.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 'BadGateway',
            description: 'The upstream service returned an unsuccessful or invalid response.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
        new OA\Response(
            response: 'GatewayTimeout',
            description: 'The upstream service could not be reached in time.',
            content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
        ),
    ]
)]
final class Responses
{
}
