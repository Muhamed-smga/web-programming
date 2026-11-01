<?php

namespace Sarajevo2B\Backend\DAO;

use InvalidArgumentException;
use PDO;
use PDOStatement;

abstract class BaseDAO
{
    private const TABLES = ['users', 'categories', 'places', 'events', 'reviews'];

    protected PDO $pdo;
    protected string $table;
    protected array $allowedColumns;

    public function __construct(PDO $pdo, string $table, array $allowedColumns)
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new InvalidArgumentException('Unsupported database table.');
        }

        $this->pdo = $pdo;
        $this->table = $table;
        $this->allowedColumns = $allowedColumns;
    }

    public function create(array $data): array
    {
        $data = $this->validateColumns($data);
        if ($data === []) {
            throw new InvalidArgumentException('At least one field is required.');
        }

        $columns = array_keys($data);
        $quotedColumns = array_map([$this, 'quoteIdentifier'], $columns);
        $placeholders = array_map(static function (string $column): string {
            return ':' . $column;
        }, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', $quotedColumns),
            implode(', ', $placeholders)
        );

        $this->execute($sql, $data);
        $id = (int) $this->pdo->lastInsertId();
        $record = $this->getById($id);

        if ($record === null) {
            throw new \RuntimeException('The inserted record could not be retrieved.');
        }

        return $record;
    }

    public function getAll(): array
    {
        $statement = $this->pdo->query(sprintf('SELECT * FROM `%s` ORDER BY `id` ASC', $this->table));
        return $statement->fetchAll();
    }

    public function getById(int $id): ?array
    {
        if ($id < 1) {
            throw new InvalidArgumentException('ID must be a positive integer.');
        }

        $statement = $this->execute(
            sprintf('SELECT * FROM `%s` WHERE `id` = :id LIMIT 1', $this->table),
            ['id' => $id]
        );
        $record = $statement->fetch();

        return $record === false ? null : $record;
    }

    public function update(int $id, array $data): ?array
    {
        if ($id < 1) {
            throw new InvalidArgumentException('ID must be a positive integer.');
        }

        $data = $this->validateColumns($data);
        if ($data === []) {
            return $this->getById($id);
        }

        $assignments = [];
        foreach (array_keys($data) as $column) {
            $assignments[] = $this->quoteIdentifier($column) . ' = :' . $column;
        }
        $data['id'] = $id;

        $this->execute(
            sprintf('UPDATE `%s` SET %s WHERE `id` = :id', $this->table, implode(', ', $assignments)),
            $data
        );

        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        if ($id < 1) {
            throw new InvalidArgumentException('ID must be a positive integer.');
        }

        $statement = $this->execute(
            sprintf('DELETE FROM `%s` WHERE `id` = :id', $this->table),
            ['id' => $id]
        );

        return $statement->rowCount() > 0;
    }

    protected function execute(string $sql, array $parameters): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        foreach ($parameters as $name => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue(':' . $name, $value, $type);
        }
        $statement->execute();

        return $statement;
    }

    private function validateColumns(array $data): array
    {
        foreach (array_keys($data) as $column) {
            if (!is_string($column) || !in_array($column, $this->allowedColumns, true)) {
                throw new InvalidArgumentException('An unsupported field was provided.');
            }
        }

        return $data;
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (!in_array($identifier, $this->allowedColumns, true)) {
            throw new InvalidArgumentException('An unsupported database column was provided.');
        }

        return '`' . $identifier . '`';
    }
}
