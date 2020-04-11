<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\{
    Width,
    Height,
};
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
                Url::any()
            )
            ->then(function($width, $height, $url) {
                $artwork = new Artwork(
                    $width,
                    $height,
                    $url
                );

                $this->assertSame($width, $artwork->width());
                $this->assertSame($height, $artwork->height());
                $this->assertSame($url, $artwork->url());
            });
    }
}
