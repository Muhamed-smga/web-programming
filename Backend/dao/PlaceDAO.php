<?php

namespace Sarajevo2B\Backend\DAO;

use PDO;

final class PlaceDAO extends BaseDAO
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'places', [
            'category_id',
            'name',
            'description',
            'address',
            'latitude',
            'longitude',
            'phone',
            'website',
            'opening_hours',
            'image_url',
        ]);
    }
}
