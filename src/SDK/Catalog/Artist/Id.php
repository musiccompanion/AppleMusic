<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Artist;

use MusicCompanion\AppleMusic\Exception\DomainException;

/**
 * @psalm-immutable
 */
final class Id
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value < 0) {
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

    public function toString(): string
    {
        return (string) $this->value;
    }
}
