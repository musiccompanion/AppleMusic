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
        $char = Set\Elements::of(...\range('a', 'z'));
        $region = Set\Elements::of(...\range('A', 'Z'));

        return Set\Composite::immutable(
            static fn(string $char1, string $char2, string $region1, string $region2) => Model::of($char1.$char2.'-'.$region1.$region2),
            $char,
            $char,
            $region,
            $region,
        )->take(100);
    }
}
