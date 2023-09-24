<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork as Model;
use Innmind\Immutable\Maybe;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Url\Url;

final class Artwork
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            static function($width, $height, $url): Model {
                return new Model(Maybe::of($width), Maybe::of($height), $url);
            },
            Set\Nullable::of(Artwork\Width::any()),
            Set\Nullable::of(Artwork\Height::any()),
            Url::any(),
        );
    }
}
