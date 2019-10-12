<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Album\Artwork;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\Artwork\Height as Model;
use Innmind\BlackBox\Set;

final class Height
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Decorate(
            static function(int $number): Model {
                return new Model($number);
            },
            new Set\NaturalNumbersExceptZero
        );
    }
}
