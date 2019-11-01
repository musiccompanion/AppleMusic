<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist as Model,
    Album as AlbumModel,
    Genre as GenreModel,
};
use Innmind\BlackBox\Set;

final class Artist
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Composite(
            static function($id, $name, $url, $genres, $albums): Model {
                return new Model($id, $name, $url, $genres, $albums);
            },
            Artist\Id::any(),
            Artist\Name::any(),
            Set\Url::any(),
            Set\Set::of(GenreModel::class, Genre::any()),
            Set\Set::of(AlbumModel\Id::class, Album\Id::any())
        );
    }
}
