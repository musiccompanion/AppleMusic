<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Album as Model;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Immutable\Set as ISet;

final class Album
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            Model::of(...),
            Album\Id::any(),
            Album\Name::any(),
            Album\Artwork::any(),
            ISet::of(Artist\Id::any()),
        );
    }
}
