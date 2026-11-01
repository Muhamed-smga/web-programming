<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class UserDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'users', ['name', 'email', 'password_hash', 'role']);
    }

    public function getByEmail(string $email): ?array
    {
        $statement = $this->execute(
            'SELECT * FROM `users` WHERE `email` = :email LIMIT 1',
            ['email' => $email]
        );
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }
}
