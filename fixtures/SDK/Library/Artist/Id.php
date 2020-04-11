<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Artist;

use MusicCompanion\AppleMusic\SDK\Library\Artist\Id as Model;
use Innmind\BlackBox\Set;

final class Id
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        $chars = Set\Regex::for('^[a-zA-Z0-9]{7}$');

        return Set\Decorate::immutable(
            static function(string $chars): Model {
                return new Model('r.'.$chars);
            },
            $chars,
        )->take(100);
    }
}
