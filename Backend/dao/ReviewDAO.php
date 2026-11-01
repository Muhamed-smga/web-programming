<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class ReviewDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'reviews', ['user_id', 'place_id', 'rating', 'comment']);
    }
}
