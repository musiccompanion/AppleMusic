<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Album as Model,
    Genre as GenreModel,
    Song as SongModel,
    Artist as ArtistModel,
};
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
            static function($id, $artwork, $name, $single, $url, $complete, $genres, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists): Model {
                return new Model($id, $artwork, $name, $single, $url, $complete, $genres, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists);
            },
            Album\Id::any(),
            Artwork::any(),
            Album\Name::any(),
            Set\Elements::of(true, false),
            Url::any(),
            Set\Elements::of(true, false),
            ISet::of(GenreModel::class, Genre::any()),
            ISet::of(SongModel\Id::class, Song\Id::any()),
            Set\Elements::of(true, false),
            PointInTime::any(),
            Album\RecordLabel::any(),
            Album\Copyright::any(),
            Album\EditorialNotes::any(),
            ISet::of(ArtistModel\Id::class, Artist\Id::any()),
        );
    }
}
