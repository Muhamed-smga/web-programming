<?php

namespace Sarajevo2B\Backend\Services;

use Sarajevo2B\Backend\DAO\EventDAO;

final class EventService
{
    private EventDAO $dao;

    public function __construct(EventDAO $dao)
    {
        $this->dao = $dao;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    public function getById(int $id): array
    {
        return $this->dao->getById($id) ?? throw new ApiException('Event not found.', 404);
    }

    public function create(array $data): array
    {
        Input::only($data, ['place_id', 'title', 'description', 'event_date', 'start_time', 'end_time', 'image_url']);
        Input::requireKeys($data, ['title', 'event_date']);

        return $this->dao->create($this->validate($data));
    }

    public function update(int $id, array $data, bool $replace = false): array
    {
        Input::only($data, ['place_id', 'title', 'description', 'event_date', 'start_time', 'end_time', 'image_url']);
        if ($replace) {
            Input::requireKeys($data, [
                'place_id', 'title', 'description', 'event_date', 'start_time', 'end_time', 'image_url',
            ]);
        }
        if ($data === []) {
            throw new ApiException('At least one field is required.', 422);
        }

        return $this->dao->update($id, $this->validate($data))
            ?? throw new ApiException('Event not found.', 404);
    }

    public function delete(int $id): void
    {
        if (!$this->dao->delete($id)) {
            throw new ApiException('Event not found.', 404);
        }
    }

    private function validate(array $data): array
    {
        $validated = [];
        if (array_key_exists('place_id', $data)) {
            $validated['place_id'] = Input::nullablePositiveInt($data, 'place_id');
        }
        if (array_key_exists('title', $data)) {
            $validated['title'] = Input::string($data, 'title', 150, true);
        }
        if (array_key_exists('description', $data)) {
            $validated['description'] = Input::string($data, 'description', 65535);
        }
        if (array_key_exists('event_date', $data)) {
            $validated['event_date'] = Input::date($data, 'event_date', true);
        }
        foreach (['start_time', 'end_time'] as $key) {
            if (array_key_exists($key, $data)) {
                $validated[$key] = Input::time($data, $key);
            }
        }
        if (array_key_exists('image_url', $data)) {
            $validated['image_url'] = Input::string($data, 'image_url', 500);
        }

        return $validated;
    }
}
