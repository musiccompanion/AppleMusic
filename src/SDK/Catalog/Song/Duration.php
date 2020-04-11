<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\Exception\DomainException;

/**
 * Expressed in milliseconds
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

    public function toInt(): int
    {
        return $this->value;
    }
}
