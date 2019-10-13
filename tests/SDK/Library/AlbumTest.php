<?php
declare(strict_types = 1);

namespace Tests\MusicComapnion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\{
    Album,
    Artist,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\{
    Album\Id,
    Album\Name,
    Album\Artwork,
    Artist as ArtistSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class AlbumTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Artwork::any(),
                Set\Set::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->take(1000)
            ->then(function($id, $name, $artwork, $artists) {
                $album = new Album($id, $name, $artwork, ...$artists);

                $this->assertSame($id, $album->id());
                $this->assertSame($name, $album->name());
                $this->assertSame($artwork, $album->artwork());
                $this->assertTrue($artists->equals($album->artists()));
            });
    }
}
