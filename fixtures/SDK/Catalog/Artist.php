<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artist as Model;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Immutable\Set as ISet;
use Fixtures\Innmind\Url\Url;

final class Artist
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            Model::of(...),
            Artist\Id::any(),
            Artist\Name::any(),
            Url::any(),
            ISet::of(Genre::any()),
            ISet::of(Album\Id::any()),
        );
    }
}
