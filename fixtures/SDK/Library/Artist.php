<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Artist as Model;
use Innmind\BlackBox\Set;

final class Artist
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            static function($id, $name): Model {
                return new Model($id, $name);
            },
            Artist\Id::any(),
            Artist\Name::any(),
        );
    }
}
