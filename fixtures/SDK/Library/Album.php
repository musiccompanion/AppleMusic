<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\{
    Album as Model,
    Artist as ArtistModel,
};
use Innmind\BlackBox\Set;

final class Album
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Composite(
            static function($id, $name, $artwork, $artists): Model {
                return new Model($id, $name, $artwork, ...$artists);
            },
            Album\Id::any(),
            Album\Name::any(),
            Album\Artwork::any(),
            Set\Set::of(ArtistModel\Id::class, Artist\Id::any())
        );
    }
}
