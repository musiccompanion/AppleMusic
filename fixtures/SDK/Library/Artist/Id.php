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
        $char = Set\Chars::of()->filter(static function(string $char): bool {
            return (bool) \preg_match('~^[a-zA-Z0-9]$~', $char);
        });

        return Set\Composite::of(
            static function(string ...$chars): Model {
                return new Model('r.'.\implode('', $chars));
            },
            ...\array_fill(0, 7, $char)
        )->take(100);
    }
}
