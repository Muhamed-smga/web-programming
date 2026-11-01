<?php

namespace Sarajevo2B\Backend\Services;

use Sarajevo2B\Backend\DAO\CategoryDAO;

final class CategoryService
{
    private CategoryDAO $dao;

    public function __construct(CategoryDAO $dao)
    {
        $this->dao = $dao;
    }

    public function getAll(): array
    {
        return $this->dao->getAll();
    }

    public function getById(int $id): array
    {
        return $this->dao->getById($id) ?? throw new ApiException('Category not found.', 404);
    }

    public function create(array $data): array
    {
        Input::only($data, ['name', 'description']);
        return $this->dao->create([
            'name' => Input::string($data, 'name', 100, true),
            'description' => Input::string($data, 'description', 65535),
        ]);
    }

    public function update(int $id, array $data, bool $replace = false): array
    {
        Input::only($data, ['name', 'description']);
        if ($replace) {
            Input::requireKeys($data, ['name', 'description']);
        }
        if ($data === []) {
            throw new ApiException('At least one field is required.', 422);
        }

        $changes = [];
        if (array_key_exists('name', $data)) {
            $changes['name'] = Input::string($data, 'name', 100, true);
        }
        if (array_key_exists('description', $data)) {
            $changes['description'] = Input::string($data, 'description', 65535);
        }

        return $this->dao->update($id, $changes) ?? throw new ApiException('Category not found.', 404);
    }

    public function delete(int $id): void
    {
        if (!$this->dao->delete($id)) {
            throw new ApiException('Category not found.', 404);
        }
    }
}
