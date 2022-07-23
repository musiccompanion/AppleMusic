<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\{
    Width,
    Height,
};
use Innmind\Url\Url as ConcreteUrl;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
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
                $artwork = new Artwork(
                    $width,
                    $height,
                    $url,
                );

                $this->assertSame($width, $artwork->width());
                $this->assertSame($height, $artwork->height());
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
                $artwork = new Artwork(
                    $width,
                    $height,
                    ConcreteUrl::of('https://is1-ssl.mzstatic.com/image/thumb/Music128/v4/1d/b0/2d/1db02d23-6e40-ae43-29c9-ff31a854e8aa/074643865326.jpg/{w}x{h}bb.jpeg'),
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
