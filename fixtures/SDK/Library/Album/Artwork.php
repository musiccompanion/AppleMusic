<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork as Model;
use Innmind\BlackBox\Set;

final class Artwork
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Composite(
            static function($width, $height, $url): Model {
                return new Model($width, $height, $url);
            },
            Artwork\Width::any(),
            Artwork\Height::any(),
            Set\Url::of()
        );
    }
}
