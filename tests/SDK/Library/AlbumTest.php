<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library;

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
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Immutable\Set as ISet;

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
                ISet::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->then(function($id, $name, $artwork, $artists) {
                $album = new Album($id, $name, $artwork, ...unwrap($artists));

                $this->assertSame($id, $album->id());
                $this->assertSame($name, $album->name());
                $this->assertTrue($album->hasArtwork());
                $this->assertSame($artwork, $album->artwork());
                $this->assertTrue($artists->equals($album->artists()));
            });
    }

    public function testAlbumMayNotHaveAnArtwork()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                ISet::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->then(function($id, $name, $artists) {
                $album = new Album($id, $name, null, ...unwrap($artists));

                $this->assertSame($id, $album->id());
                $this->assertSame($name, $album->name());
                $this->assertFalse($album->hasArtwork());
                $this->assertTrue($artists->equals($album->artists()));
            });
    }
}
