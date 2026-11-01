<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class EventDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'events', [
            'place_id',
            'title',
            'description',
            'event_date',
            'start_time',
            'end_time',
            'image_url',
        ]);
    }
}
