<?php

namespace Sarajevo2B\Backend\Middleware;

use Flight;
use Sarajevo2B\Backend\Services\ApiException;

final class RoleMiddleware
{
    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function before(array $params): void
    {
        $user = Flight::get('currentUser');
        if (!is_array($user) || ($user['role'] ?? null) !== $this->role) {
            throw new ApiException('You are not authorized to perform this action.', 403);
        }
    }
}
