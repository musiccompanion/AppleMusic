<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\Artwork as Model;
use Innmind\BlackBox\Set;

final class Artwork
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Composite(
            static function($width, $height, $url, $background, $text1, $text2, $text3, $text4): Model {
                return new Model($width, $height, $url, $background, $text1, $text2, $text3, $text4);
            },
            Artwork\Width::any(),
            Artwork\Height::any(),
            Set\Url::of(),
            Set\Colour::of(),
            Set\Colour::of(),
            Set\Colour::of(),
            Set\Colour::of(),
            Set\Colour::of()
        );
    }
}
