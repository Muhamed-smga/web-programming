<?php

namespace Sarajevo2B\Backend\Services;

use Sarajevo2B\Backend\DAO\UserDAO;

final class UserService
{
    private UserDAO $dao;

    public function __construct(UserDAO $dao)
    {
        $this->dao = $dao;
    }

    public function register(array $data): array
    {
        Input::only($data, ['name', 'email', 'password']);
        return $this->create($data, false);
    }

    public function createByAdmin(array $data): array
    {
        Input::only($data, ['name', 'email', 'password', 'role']);
        return $this->create($data, true);
    }

    public function getAll(): array
    {
        return array_map([$this, 'withoutPasswordHash'], $this->dao->getAll());
    }

    public function getById(int $id): array
    {
        $user = $this->dao->getById($id);
        if ($user === null) {
            throw new ApiException('User not found.', 404);
        }

        return $this->withoutPasswordHash($user);
    }

    public function update(int $id, array $data, bool $isAdmin, bool $replace = false): array
    {
        $fields = $isAdmin ? ['name', 'email', 'password', 'role'] : ['name', 'email', 'password'];
        Input::only($data, $fields);
        if ($replace) {
            Input::requireKeys($data, ['name', 'email']);
            if ($isAdmin) {
                Input::requireKeys($data, ['role']);
            }
        }
        if ($data === []) {
            throw new ApiException('At least one field is required.', 422);
        }

        $changes = $this->validateUserFields($data, $isAdmin);
        if (array_key_exists('password', $data)) {
            $changes['password_hash'] = $this->hashPassword($data['password']);
            unset($changes['password']);
        }

        $user = $this->dao->update($id, $changes);
        if ($user === null) {
            throw new ApiException('User not found.', 404);
        }

        return $this->withoutPasswordHash($user);
    }

    public function delete(int $id): void
    {
        if (!$this->dao->delete($id)) {
            throw new ApiException('User not found.', 404);
        }
    }

    private function create(array $data, bool $allowRole): array
    {
        Input::requireKeys($data, ['name', 'email', 'password']);
        $fields = $this->validateUserFields($data, $allowRole);
        $fields['password_hash'] = $this->hashPassword($data['password']);
        $fields['role'] = $allowRole && array_key_exists('role', $data) ? $fields['role'] : 'user';
        unset($fields['password']);

        return $this->withoutPasswordHash($this->dao->create($fields));
    }

    private function validateUserFields(array $data, bool $allowRole): array
    {
        $fields = [];
        if (array_key_exists('name', $data)) {
            $fields['name'] = Input::string($data, 'name', 100, true);
        }
        if (array_key_exists('email', $data)) {
            $email = Input::string($data, 'email', 150, true);
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new ApiException('The email address is invalid.', 422);
            }
            $fields['email'] = strtolower($email);
        }
        if (array_key_exists('password', $data)) {
            $password = $data['password'];
            if (!is_string($password) || strlen($password) < 8 || strlen($password) > 255) {
                throw new ApiException('The password must contain between 8 and 255 characters.', 422);
            }
            $fields['password'] = $password;
        }
        if (array_key_exists('role', $data)) {
            if (!$allowRole || !in_array($data['role'], ['admin', 'user'], true)) {
                throw new ApiException('The role is invalid.', 422);
            }
            $fields['role'] = $data['role'];
        }

        return $fields;
    }

    private function hashPassword(string $password): string
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new \RuntimeException('Password hashing failed.');
        }

        return $hash;
    }

    private function withoutPasswordHash(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }
}
