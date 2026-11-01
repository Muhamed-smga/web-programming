<?php

namespace Sarajevo2B\Backend\Middleware;

use Flight;
use Sarajevo2B\Backend\Services\AuthService;
use Sarajevo2B\Backend\Services\ApiException;

final class AuthMiddleware
{
    private AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function before(array $params): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
            throw new ApiException('A Bearer authentication token is required.', 401);
        }

        Flight::set('currentUser', $this->auth->authenticate($matches[1]));
    }
}
