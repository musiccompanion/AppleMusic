<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\Time\Format;

/**
 * @psalm-immutable
 */
final class ReleaseDate implements Format\Custom
{
    #[\Override]
    public function normalize(): Format
    {
        return Format::of('Y-m-d');
    }
}
