<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\SDK\Library\Song\Duration as Model;
use Innmind\BlackBox\Set;

final class Duration
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
