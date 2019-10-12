<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\RecordLabel as Model;
use Innmind\BlackBox\Set;

final class RecordLabel
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return new Set\Decorate(
            static function(string $string): Model {
                return new Model($string);
            },
            new Set\Strings
        );
    }
}
