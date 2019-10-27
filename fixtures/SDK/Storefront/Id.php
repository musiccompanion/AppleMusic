<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\SDK\Storefront\Id as Model;
use Innmind\BlackBox\Set;

final class Id
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        $char = Set\Chars::of()->filter(static function($char): bool {
            return \in_array($char, \range('a', 'z'), true);
        });

        return Set\Composite::of(
            static function(string $char1, string $char2): Model {
                return new Model($char1.$char2);
            },
            $char,
            $char
        )->take(100);
    }
}
