<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\TrackNumber as Model;
use Innmind\BlackBox\Set;

final class TrackNumber
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Decorate::immutable(
            Model::of(...),
            Set\NaturalNumbersExceptZero::any(),
        );
    }
}
