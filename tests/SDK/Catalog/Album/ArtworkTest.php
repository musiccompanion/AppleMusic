<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\Artwork;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Album\Artwork\{
    Width,
    Height,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ArtworkTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Width::any(),
                Height::any(),
                Set\Url::of(),
                Set\Colour::of(),
                Set\Colour::of(),
                Set\Colour::of(),
                Set\Colour::of(),
                Set\Colour::of()
            )
            ->then(function($width, $height, $url, $background, $text1, $text2, $text3, $text4) {
                $artwork = new Artwork(
                    $width,
                    $height,
                    $url,
                    $background,
                    $text1,
                    $text2,
                    $text3,
                    $text4
                );

                $this->assertSame($width, $artwork->width());
                $this->assertSame($height, $artwork->height());
                $this->assertSame($background, $artwork->backgroundColor());
                $this->assertSame($text1, $artwork->textColor1());
                $this->assertSame($text2, $artwork->textColor2());
                $this->assertSame($text3, $artwork->textColor3());
                $this->assertSame($text4, $artwork->textColor4());
            });
    }
}
