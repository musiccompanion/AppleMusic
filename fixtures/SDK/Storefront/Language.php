<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Storefront;

use MusicCompanion\AppleMusic\SDK\Storefront\Language as Model;
use Innmind\BlackBox\Set;

final class Language
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        $char = Set\Chars::of()->filter(static function($char): bool {
            return \in_array($char, \range('a', 'z'), true);
        });
        $region = Set\Chars::of()->filter(static function($char): bool {
            return \in_array($char, \range('A', 'Z'), true);
        });

        return Set\Composite::of(
            static function(string $char1, string $char2, string $region1, string $region2): Model {
                return new Model($char1.$char2.'-'.$region1.$region2);
            },
            $char,
            $char,
            $region,
            $region
        )->take(100);
    }
}
