<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Id
{
    private string $value;

    private function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^i\.[a-zA-Z0-9]+$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    /**
     * @psalm-pure
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
