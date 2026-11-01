<?php

namespace Sarajevo2B\Backend\Services;

use Sarajevo2B\Backend\DAO\PlaceDAO;

final class PlaceService
{
    private PlaceDAO $dao;

    public function __construct(PlaceDAO $dao)
    {
        $this->dao = $dao;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    public function getById(int $id): array
    {
        return $this->dao->getById($id) ?? throw new ApiException('Place not found.', 404);
    }

    public function create(array $data): array
    {
        Input::only($data, [
            'category_id', 'name', 'description', 'address', 'latitude', 'longitude',
            'phone', 'website', 'opening_hours', 'image_url',
        ]);
        Input::requireKeys($data, ['category_id', 'name', 'address']);

        return $this->dao->create($this->validate($data));
    }

    public function update(int $id, array $data, bool $replace = false): array
    {
        Input::only($data, [
            'category_id', 'name', 'description', 'address', 'latitude', 'longitude',
            'phone', 'website', 'opening_hours', 'image_url',
        ]);
        if ($replace) {
            Input::requireKeys($data, [
                'category_id', 'name', 'description', 'address', 'latitude', 'longitude',
                'phone', 'website', 'opening_hours', 'image_url',
            ]);
        }
        if ($data === []) {
            throw new ApiException('At least one field is required.', 422);
        }

        return $this->dao->update($id, $this->validate($data))
            ?? throw new ApiException('Place not found.', 404);
    }

    public function delete(int $id): void
    {
        if (!$this->dao->delete($id)) {
            throw new ApiException('Place not found.', 404);
        }
    }

    private function validate(array $data): array
    {
        $validated = [];
        if (array_key_exists('category_id', $data)) {
            $validated['category_id'] = Input::positiveInt($data, 'category_id', true);
        }
        if (array_key_exists('name', $data)) {
            $validated['name'] = Input::string($data, 'name', 150, true);
        }
        if (array_key_exists('description', $data)) {
            $validated['description'] = Input::string($data, 'description', 65535);
        }
        if (array_key_exists('address', $data)) {
            $validated['address'] = Input::string($data, 'address', 255, true);
        }
        if (array_key_exists('latitude', $data)) {
            $validated['latitude'] = Input::nullableNumber($data, 'latitude', -90, 90);
        }
        if (array_key_exists('longitude', $data)) {
            $validated['longitude'] = Input::nullableNumber($data, 'longitude', -180, 180);
        }
        foreach (['phone' => 30, 'website' => 255, 'opening_hours' => 65535, 'image_url' => 500] as $key => $max) {
            if (array_key_exists($key, $data)) {
                $validated[$key] = Input::string($data, $key, $max);
            }
        }

        return $validated;
    }
}
