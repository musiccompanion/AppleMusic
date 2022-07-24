<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Language
{
    private string $value;

    private function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[a-z]{2}(-[a-zA-Z]+){0,2}$~')) {
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
