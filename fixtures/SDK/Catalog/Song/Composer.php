<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\Composer as Model;
use Innmind\BlackBox\Set;

final class Composer
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Decorate::immutable(
            static function(string $name): Model {
                return new Model($name);
            },
            Set\Strings::any(),
        );
    }
}
