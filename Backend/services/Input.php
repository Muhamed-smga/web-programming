<?php

namespace Sarajevo2B\Backend\Services;

final class Input
{
    public static function only(array $data, array $allowed): array
    {
        if (array_diff(array_keys($data), $allowed) !== []) {
            throw new ApiException('One or more fields are not allowed.', 422);
        }

        return $data;
    }

    public static function requireKeys(array $data, array $required): void
    {
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                throw new ApiException(sprintf('The %s field is required.', $key), 422);
            }
        }
    }

    public static function string(array $data, string $key, int $maxLength, bool $required = false): ?string
    {
        if (!array_key_exists($key, $data)) {
            if ($required) {
                throw new ApiException(sprintf('The %s field is required.', $key), 422);
            }
            return null;
        }

        $value = $data[$key];
        if ($value === null && !$required) {
            return null;
        }
        if (!is_string($value)) {
            throw new ApiException(sprintf('The %s field must be a string.', $key), 422);
        }

        $value = trim($value);
        if (($required && $value === '') || mb_strlen($value) > $maxLength) {
            throw new ApiException(sprintf('The %s field is invalid.', $key), 422);
        }

        return $value;
    }

    public static function positiveInt(array $data, string $key, bool $required = false): ?int
    {
        if (!array_key_exists($key, $data)) {
            if ($required) {
                throw new ApiException(sprintf('The %s field is required.', $key), 422);
            }
            return null;
        }

        $value = $data[$key];
        if (!is_int($value) || $value < 1) {
            throw new ApiException(sprintf('The %s field must be a positive integer.', $key), 422);
        }

        return $value;
    }

    public static function nullablePositiveInt(array $data, string $key): ?int
    {
        if (array_key_exists($key, $data) && $data[$key] === null) {
            return null;
        }

        return self::positiveInt($data, $key);
    }

    public static function nullableNumber(array $data, string $key, float $minimum, float $maximum): ?float
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        $value = $data[$key];
        if ($value === null) {
            return null;
        }
        if ((!is_int($value) && !is_float($value)) || !is_finite((float) $value)) {
            throw new ApiException(sprintf('The %s field must be a number or null.', $key), 422);
        }

        $value = (float) $value;
        if ($value < $minimum || $value > $maximum) {
            throw new ApiException(sprintf('The %s field is out of range.', $key), 422);
        }

        return $value;
    }

    public static function date(array $data, string $key, bool $required = false): ?string
    {
        $value = self::string($data, $key, 10, $required);
        if ($value === null) {
            return null;
        }

        $parts = explode('-', $value);
        if (count($parts) !== 3 || !checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
            throw new ApiException(sprintf('The %s field must be a valid YYYY-MM-DD date.', $key), 422);
        }

        return $value;
    }

    public static function time(array $data, string $key): ?string
    {
        $value = self::string($data, $key, 8);
        if ($value === null) {
            return null;
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value)) {
            throw new ApiException(sprintf('The %s field must be a valid time.', $key), 422);
        }

        return strlen($value) === 5 ? $value . ':00' : $value;
    }

    public static function rating(array $data, string $key = 'rating', bool $required = false): ?int
    {
        $value = self::positiveInt($data, $key, $required);
        if ($value !== null && ($value < 1 || $value > 5)) {
            throw new ApiException('The rating must be an integer from 1 through 5.', 422);
        }

        return $value;
    }
}
