<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\SDK\Library\Song\TrackNumber as Model;
use Innmind\BlackBox\Set;

final class TrackNumber
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\NaturalNumbersExceptZero::any()->map(Model::of(...));
    }
}
