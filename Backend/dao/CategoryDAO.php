<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class CategoryDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'categories', ['name', 'description']);
    }
}
