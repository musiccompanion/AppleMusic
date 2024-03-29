<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\Height as Model;
use Innmind\BlackBox\Set;

final class Height
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\NaturalNumbersExceptZero::any()->map(Model::of(...));
    }
}
