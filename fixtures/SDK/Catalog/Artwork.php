<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork as Model;
use Innmind\Immutable\Maybe;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Colour\Colour;
use Fixtures\Innmind\Url\Url;

final class Artwork
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            Model::of(...),
            Artwork\Width::any(),
            Artwork\Height::any(),
            Url::any(),
            Set\Nullable::of(Colour::any())->map(Maybe::of(...)),
            Set\Nullable::of(Colour::any())->map(Maybe::of(...)),
            Set\Nullable::of(Colour::any())->map(Maybe::of(...)),
            Set\Nullable::of(Colour::any())->map(Maybe::of(...)),
            Set\Nullable::of(Colour::any())->map(Maybe::of(...)),
        );
    }
}
