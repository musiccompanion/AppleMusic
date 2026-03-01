<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Album;
use Innmind\Immutable\Maybe;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\{
    Album\Id,
    Album\Name,
    Album\Artwork,
    Artist as ArtistSet,
};
use Innmind\BlackBox\PHPUnit\{
    BlackBox,
    Framework\TestCase,
};
use Fixtures\Innmind\Immutable\Set as ISet;

class AlbumTest extends TestCase
{
    use BlackBox;

    public function testInterface(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Id::any(),
                Name::any(),
                Artwork::any(),
                ISet::of(ArtistSet\Id::any()),
            )
            ->prove(function($id, $name, $artwork, $artists) {
                $album = Album::of($id, $name, Maybe::just($artwork), $artists);

                $this->assertSame($id, $album->id());
                $this->assertSame($name, $album->name());
                $this->assertSame($artwork, $album->artwork()->match(
                    static fn($artwork) => $artwork,
                    static fn() => null,
                ));
                $this->assertTrue($artists->equals($album->artists()));
            });
    }

    public function testAlbumMayNotHaveAnArtwork(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Id::any(),
                Name::any(),
                ISet::of(ArtistSet\Id::any()),
            )
            ->prove(function($id, $name, $artists) {
                $album = Album::of($id, $name, Maybe::nothing(), $artists);

                $this->assertSame($id, $album->id());
                $this->assertSame($name, $album->name());
                $this->assertFalse($album->artwork()->match(
                    static fn() => true,
                    static fn() => false,
                ));
                $this->assertTrue($artists->equals($album->artists()));
            });
    }
}
