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

final class Album
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Composite(
            static function($id, $artwork, $name, $single, $url, $complete, $genres, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists): Model {
                return new Model($id, $artwork, $name, $single, $url, $complete, $genres, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists);
            },
            Album\Id::any(),
            Artwork::any(),
            Album\Name::any(),
            Set\Elements::of(true, false),
            Set\Url::any(),
            Set\Elements::of(true, false),
            Set\Set::of(GenreModel::class, Genre::any()),
            Set\Set::of(SongModel\Id::class, Song\Id::any()),
            Set\Elements::of(true, false),
            Set\PointInTime::any(),
            Album\RecordLabel::any(),
            Album\Copyright::any(),
            Album\EditorialNotes::any(),
            Set\Set::of(ArtistModel\Id::class, Artist\Id::any())
        );
    }
}
