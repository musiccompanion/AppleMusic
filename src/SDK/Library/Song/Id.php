<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\Exception\DomainException;
use Innmind\Immutable\Str;

final class Id
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^i\.[a-zA-Z0-9]{14}$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
