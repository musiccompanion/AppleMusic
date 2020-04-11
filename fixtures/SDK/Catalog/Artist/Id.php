<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Artist;

use MusicCompanion\AppleMusic\SDK\Catalog\Artist\Id as Model;
use Innmind\BlackBox\Set;

final class Id
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
            Set\NaturalNumbers::any(),
        );
    }
}
