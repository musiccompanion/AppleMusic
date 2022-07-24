<?php
declare(strict_types = 1);

namespace Fixtures\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork as Model;
use Innmind\Immutable\Maybe;
use Innmind\BlackBox\Set;
use Fixtures\Innmind\Colour\Colour;
use Fixtures\Innmind\Url\Url;

final class Artwork
{
    /**
     * @return Set<Model>
     */
    public static function any(): Set
    {
        return Set\Composite::immutable(
            static function($width, $height, $url, $background, $text1, $text2, $text3, $text4): Model {
                return new Model(
                    $width,
                    $height,
                    $url,
                    Maybe::of($background),
                    Maybe::of($text1),
                    Maybe::of($text2),
                    Maybe::of($text3),
                    Maybe::of($text4),
                );
            },
            Artwork\Width::any(),
            Artwork\Height::any(),
            Url::any(),
            new Set\Either(
                Colour::any(),
                Set\Elements::of(null),
            ),
            new Set\Either(
                Colour::any(),
                Set\Elements::of(null),
            ),
            new Set\Either(
                Colour::any(),
                Set\Elements::of(null),
            ),
            new Set\Either(
                Colour::any(),
                Set\Elements::of(null),
            ),
            new Set\Either(
                Colour::any(),
                Set\Elements::of(null),
            ),
        );
    }
}
