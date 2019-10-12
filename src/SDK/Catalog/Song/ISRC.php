<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * International Standard Recording Code
 *
 * @see https://en.wikipedia.org/wiki/International_Standard_Recording_Code#Format
 */
final class ISRC
{
    private $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[A-Z]{2}[A-Z0-9]{3}\d{7}$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
