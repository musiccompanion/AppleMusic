<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Library\Song;

use MusicCompanion\AppleMusic\SDK\Library\Song\Id as Model;
use Innmind\BlackBox\Set;

final class Id
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        $chars = Set\Decorate::immutable(
            static fn(array $chars) => \implode('', $chars),
            Set\Sequence::of(
                Set\Decorate::immutable(
                    static fn($ord) => \chr($ord),
                    Set\Either::any(
                        Set\Integers::between(48, 57), // 0-9
                        Set\Integers::between(65, 90), // A-Z
                        Set\Integers::between(97, 122), // a-z
                    ),
                ),
            )->between(1, 15),
        );

        return Set\Decorate::immutable(
            static function(string $chars): Model {
                return new Model('i.'.$chars);
            },
            $chars,
        )->take(100);
    }
}
