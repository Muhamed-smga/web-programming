<?php

namespace Sarajevo2B\Backend\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Sarajevo2B\Backend\DAO\UserDAO;

final class AuthService
{
    private UserDAO $users;
    private string $secret;
    private int $ttl;

    public function __construct(UserDAO $users, string $secret, int $ttl)
    {
        $this->users = $users;
        $this->secret = $secret;
        $this->ttl = $ttl;
    }

    public function login(array $data): array
    {
        Input::only($data, ['email', 'password']);
        Input::requireKeys($data, ['email', 'password']);

        $email = Input::string($data, 'email', 150, true);
        $password = $data['password'];
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || !is_string($password)) {
            throw new ApiException('Email or password is incorrect.', 401);
        }

        $user = $this->users->getByEmail(strtolower($email));
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            throw new ApiException('Email or password is incorrect.', 401);
        }

        return [
            'token' => $this->issueToken((int) $user['id']),
            'user' => $this->publicUser($user),
        ];
    }

    public function authenticate(string $token): array
    {
        try {
            $claims = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Throwable $exception) {
            throw new ApiException('The authentication token is invalid or expired.', 401);
        }

        $userId = isset($claims->sub) && ctype_digit((string) $claims->sub)
            ? (int) $claims->sub
            : 0;
        if ($userId < 1) {
            throw new ApiException('The authentication token is invalid or expired.', 401);
        }

        $user = $this->users->getById($userId);
        if ($user === null) {
            throw new ApiException('The authentication token is invalid or expired.', 401);
        }

        return $this->publicUser($user);
    }

    private function issueToken(int $userId): string
    {
        $now = time();
        return JWT::encode([
            'iss' => 'sarajevo2b',
            'iat' => $now,
            'exp' => $now + $this->ttl,
            'sub' => (string) $userId,
        ], $this->secret, 'HS256');
    }

    private function publicUser(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }
}
