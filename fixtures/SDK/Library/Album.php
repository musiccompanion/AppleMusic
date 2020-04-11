<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\{
    Album as Model,
    Artist as ArtistModel,
};
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
            static function($id, $name, $artwork, $artists): Model {
                return new Model($id, $name, $artwork, ...$artists);
            },
            Album\Id::any(),
            Album\Name::any(),
            Album\Artwork::any(),
            ISet::of(ArtistModel\Id::class, Artist\Id::any()),
        );
    }
}
