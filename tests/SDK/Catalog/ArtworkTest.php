<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Artwork\{
    Width,
    Height,
};
use Innmind\Url\Url as ConcreteUrl;
use Innmind\Immutable\Maybe;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Url;
use Fixtures\Innmind\Colour\Colour;

class ArtworkTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Width::any(),
                Height::any(),
                Url::any(),
                Colour::any(),
                Colour::any(),
                Colour::any(),
                Colour::any(),
                Colour::any(),
            )
            ->then(function($width, $height, $url, $background, $text1, $text2, $text3, $text4) {
                $artwork = new Artwork(
                    $width,
                    $height,
                    $url,
                    Maybe::of($background),
                    Maybe::of($text1),
                    Maybe::of($text2),
                    Maybe::of($text3),
                    Maybe::of($text4),
                );

                $this->assertSame($width, $artwork->width());
                $this->assertSame($height, $artwork->height());
                $this->assertSame($url, $artwork->url());
                $this->assertSame($background, $artwork->backgroundColor()->match(
                    static fn($color) => $color,
                    static fn() => null,
                ));
                $this->assertSame($text1, $artwork->textColor1()->match(
                    static fn($color) => $color,
                    static fn() => null,
                ));
                $this->assertSame($text2, $artwork->textColor2()->match(
                    static fn($color) => $color,
                    static fn() => null,
                ));
                $this->assertSame($text3, $artwork->textColor3()->match(
                    static fn($color) => $color,
                    static fn() => null,
                ));
                $this->assertSame($text4, $artwork->textColor4()->match(
                    static fn($color) => $color,
                    static fn() => null,
                ));
            });
    }

    public function testOfSize()
    {
        $this
            ->forAll(
                Width::any(),
                Height::any(),
                Colour::any(),
                Colour::any(),
                Colour::any(),
                Colour::any(),
                Colour::any(),
            )
            ->then(function($width, $height, $background, $text1, $text2, $text3, $text4) {
                $artwork = new Artwork(
                    $width,
                    $height,
                    ConcreteUrl::of('https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg'),
                    Maybe::of($background),
                    Maybe::of($text1),
                    Maybe::of($text2),
                    Maybe::of($text3),
                    Maybe::of($text4),
                );

                $url = $artwork->ofSize(
                    new Artwork\Width(42),
                    new Artwork\Height(24),
                );

                $this->assertInstanceOf(ConcreteUrl::class, $url);
                $this->assertSame(
                    'https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/42x24bb.jpeg',
                    $url->toString(),
                );
            });
    }
}
