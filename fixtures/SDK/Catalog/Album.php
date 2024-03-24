<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Album as Model;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Immutable\Set as ISet;
use Fixtures\Innmind\Url\Url;
use Fixtures\Innmind\TimeContinuum\Earth\PointInTime;

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
            Set\Either::any(
                Artwork::any(),
                Set\Elements::of(null),
            ),
            Album\Name::any(),
            Set\Elements::of(true, false),
            Url::any(),
            Set\Elements::of(true, false),
            ISet::of(Genre::any()),
            ISet::of(Song\Id::any()),
            Set\Elements::of(true, false),
            PointInTime::any(),
            Album\RecordLabel::any(),
            Album\Copyright::any(),
            Album\EditorialNotes::any(),
            ISet::of(Artist\Id::any()),
        );
    }
}
