<?php

namespace Sarajevo2B\Backend\Services;

use Sarajevo2B\Backend\DAO\ReviewDAO;
use Sarajevo2B\Backend\DAO\PlaceDAO;

final class ReviewService
{
    private ReviewDAO $dao;
    private PlaceDAO $placeDAO;

    public function __construct(ReviewDAO $dao, PlaceDAO $placeDAO)
    {
        $this->dao = $dao;
        $this->placeDAO = $placeDAO;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    public function getById(int $id): array
    {
        return $this->dao->getById($id) ?? throw new ApiException('Review not found.', 404);
    }

    public function create(int $userId, array $data): array
    {
        Input::only($data, ['place_id', 'rating', 'comment']);
        Input::requireKeys($data, ['place_id', 'rating']);
        $validated = $this->validate($data);
        if ($this->placeDAO->getById($validated['place_id']) === null) {
            throw new ApiException('Place not found.', 400);
        }
        if ($this->dao->existsForUserAndPlace($userId, $validated['place_id'])) {
            throw new ApiException('You have already reviewed this place.', 400);
        }
        $validated['user_id'] = $userId;

        try {
            return $this->dao->create($validated);
        } catch (\PDOException $exception) {
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);
            if ((string) $exception->getCode() === '23000' && $driverCode === 1062) {
                throw new ApiException('You have already reviewed this place.', 400);
            }

            throw $exception;
        }
    }

    public function update(int $id, array $data): array
    {
        Input::only($data, ['rating', 'comment']);
        if ($data === []) {
            throw new ApiException('At least one field is required.', 400);
        }
        $validated = $this->validate($data);

        return $this->dao->update($id, $validated)
            ?? throw new ApiException('Review not found.', 404);
    }

    public function delete(int $id): void
    {
        if (!$this->dao->delete($id)) {
            throw new ApiException('Review not found.', 404);
        }
    }

    private function validate(array $data): array
    {
        $validated = [];
        if (array_key_exists('place_id', $data)) {
            $validated['place_id'] = Input::positiveInt($data, 'place_id', true);
        }
        if (array_key_exists('rating', $data)) {
            $validated['rating'] = Input::rating($data, 'rating', true);
        }
        if (array_key_exists('comment', $data)) {
            $validated['comment'] = Input::string($data, 'comment', 65535);
        }

        return $validated;
    }
}
