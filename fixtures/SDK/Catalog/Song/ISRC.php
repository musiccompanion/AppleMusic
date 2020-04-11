<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Song;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\ISRC as Model;
use Innmind\BlackBox\Set;

final class ISRC
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        $c = Set\Elements::of(...\range('A', 'Z'));
        $x = Set\Elements::of(...\range('A', 'Z'), ...\range(0, 9));
        $y = Set\Elements::of(...\range(0, 9));
        $n = Set\Elements::of(...\range(0, 9));

        return Set\Composite::immutable(
            static function(...$bits): Model {
                return new Model(\implode('', $bits));
            },
            $c,
            $c,
            $x,
            $x,
            $x,
            $y,
            $y,
            $n,
            $n,
            $n,
            $n,
            $n,
        );
    }
}
