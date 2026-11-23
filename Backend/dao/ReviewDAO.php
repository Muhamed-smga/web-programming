<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class ReviewDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'reviews', ['user_id', 'place_id', 'rating', 'comment']);
    }

    public function existsForUserAndPlace(int $userId, int $placeId): bool
    {
        $statement = $this->execute(
            'SELECT 1 FROM `reviews` WHERE `user_id` = :user_id AND `place_id` = :place_id LIMIT 1',
            ['user_id' => $userId, 'place_id' => $placeId]
        );

        return $statement->fetchColumn() !== false;
    }
}
