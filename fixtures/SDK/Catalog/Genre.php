<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Genre as Model;
use Innmind\BlackBox\Set;

final class Genre
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Decorate::immutable(
            Model::of(...),
            Set\Strings::any(),
        );
    }
}
