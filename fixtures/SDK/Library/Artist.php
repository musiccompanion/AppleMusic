<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Artist as Model;
use Innmind\Immutable\Maybe;
use Innmind\BlackBox\Set;

final class Artist
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            Model::of(...),
            Artist\Id::any(),
            Artist\Name::any(),
            Set\Elements::of(Maybe::nothing()),
        );
    }
}
