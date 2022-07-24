<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\TimeContinuum\Format;

/**
 * @psalm-immutable
 */
final class ReleaseDate implements Format
{
    public function toString(): string
    {
        return 'Y-m-d';
    }
}
