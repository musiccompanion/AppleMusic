<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Artwork;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork\Width as Model;
use Innmind\BlackBox\Set;

final class Width
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\NaturalNumbersExceptZero::any()->map(Model::of(...));
    }
}
