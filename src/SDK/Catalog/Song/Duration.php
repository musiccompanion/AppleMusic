<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\Exception\DomainException;

/**
 * Expressed in milliseconds
 * @psalm-immutable
 */
final class Duration
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value < 1) {
            throw new DomainException((string) $value);
        }

        $this->value = $value;
    }

    /**
     * @psalm-pure
     */
    public static function of(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
