<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\Id as Model;
use Innmind\BlackBox\Set;

final class Id
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Decorate::immutable(
            Model::of(...),
            Set\NaturalNumbers::any(),
        );
    }
}
