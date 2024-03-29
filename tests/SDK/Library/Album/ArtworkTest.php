<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\{
    Width,
    Height,
};
use Innmind\Url\Url as ConcreteUrl;
use Innmind\Immutable\Maybe;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;
use Fixtures\Innmind\Url\Url;

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
            )
            ->then(function($width, $height, $url) {
                $artwork = Artwork::of(
                    Maybe::of($width),
                    Maybe::of($height),
                    $url,
                );

                $this->assertSame($width, $artwork->width()->match(
                    static fn($width) => $width,
                    static fn() => null,
                ));
                $this->assertSame($height, $artwork->height()->match(
                    static fn($height) => $height,
                    static fn() => null,
                ));
                $this->assertSame($url, $artwork->url());
            });
    }

    public function testOfSize()
    {
        $this
            ->forAll(
                Width::any(),
                Height::any(),
            )
            ->then(function($width, $height) {
                $artwork = Artwork::of(
                    Maybe::of($width),
                    Maybe::of($height),
                    ConcreteUrl::of('https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg'),
                );

                $url = $artwork->ofSize(
                    Artwork\Width::of(42),
                    Artwork\Height::of(24),
                );

                $this->assertInstanceOf(ConcreteUrl::class, $url);
                $this->assertSame(
                    'https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/42x24bb.jpeg',
                    $url->toString(),
                );
            });
    }
}
