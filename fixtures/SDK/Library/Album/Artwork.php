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
            Model::of(...),
            Set\Nullable::of(Artwork\Width::any())->map(Maybe::of(...)),
            Set\Nullable::of(Artwork\Height::any())->map(Maybe::of(...)),
            Url::any(),
        );
    }
}
