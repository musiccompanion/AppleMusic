<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\Exception\DomainException;

/**
 * In milliseconds
 */
final class Duration
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 1) {
            throw new DomainException((string) $value);
        }

        $this->value = $value;
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }
}
