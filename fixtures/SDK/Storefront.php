<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Storefront as Model;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Immutable\Set as ISet;

final class Storefront
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            static function($id, $name, $default, $languages): Model {
                return new Model($id, $name, $default, $languages);
            },
            Storefront\Id::any(),
            Storefront\Name::any(),
            Storefront\Language::any(),
            ISet::of(Storefront\Language::any()),
        )->take(100);
    }
}
