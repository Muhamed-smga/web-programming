<?php

namespace Sarajevo2B\Backend\Services;

use Sarajevo2B\Backend\DAO\ReviewDAO;

final class ReviewService
{
    private ReviewDAO $dao;

    public function __construct(ReviewDAO $dao)
    {
        $this->dao = $dao;
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
        $validated['user_id'] = $userId;

        return $this->dao->create($validated);
    }

    public function update(int $id, array $data): array
    {
        Input::only($data, ['rating', 'comment']);
        if ($data === []) {
            throw new ApiException('At least one field is required.', 422);
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
