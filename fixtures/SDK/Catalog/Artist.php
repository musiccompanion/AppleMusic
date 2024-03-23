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
        return new Set\Composite(
            static function($id, $name, $url, $genres, $albums): Model {
                return new Model($id, $name, $url, $genres, $albums);
            },
            Artist\Id::any(),
            Artist\Name::any(),
            Url::any(),
            ISet::of(Genre::any()),
            ISet::of(Album\Id::any()),
        );
    }
}
