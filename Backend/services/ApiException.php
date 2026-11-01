<?php

namespace Sarajevo2B\Backend\Services;

use RuntimeException;

final class ApiException extends RuntimeException
{
    private int $status;

    public function __construct(string $message, int $status)
    {
        parent::__construct($message);
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
