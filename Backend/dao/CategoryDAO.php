<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class CategoryDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'categories', ['name', 'description']);
    }

    public function hasPlaces(int $categoryId): bool
    {
        $statement = $this->execute(
            'SELECT 1 FROM `places` WHERE `category_id` = :category_id LIMIT 1',
            ['category_id' => $categoryId]
        );

        return $statement->fetchColumn() !== false;
    }
}
