<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Artwork;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork\Height as Model;
use Innmind\BlackBox\Set;

final class Height
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Decorate::immutable(
            static function(int $number): Model {
                return new Model($number);
            },
            Set\NaturalNumbersExceptZero::any(),
        );
    }
}
