<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Artwork;

use MusicCompanion\AppleMusic\Exception\DomainException;

final class Width
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value < 0) {
            throw new DomainException((string) $value);
        }

        $this->value = $value;
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
