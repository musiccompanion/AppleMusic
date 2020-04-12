<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork as Model;
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
                return new Model($width, $height, $url);
            },
            new Set\Either(
                Artwork\Width::any(),
                Set\Elements::of(null),
            ),
            new Set\Either(
                Artwork\Height::any(),
                Set\Elements::of(null),
            ),
            Url::any(),
        );
    }
}
