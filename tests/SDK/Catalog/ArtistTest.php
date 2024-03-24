<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist,
};
use Innmind\Immutable\{
    Maybe,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist\Id,
    Artist\Name,
    Genre as GenreSet,
    Album as AlbumSet,
    Artwork,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\PHPUnit\BlackBox;
use Fixtures\Innmind\Immutable\Set as ISet;
use Fixtures\Innmind\Url\Url;

class ArtistTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Url::any(),
                ISet::of(GenreSet::any()),
                ISet::of(AlbumSet\Id::any()),
                Artwork::any(),
            )
            ->then(function($id, $name, $url, $genres, $albums, $artwork) {
                $artist = Artist::of(
                    $id,
                    $name,
                    $url,
                    $genres,
                    $albums,
                    Maybe::of($artwork),
                );

                $this->assertSame($id, $artist->id());
                $this->assertSame($name, $artist->name());
                $this->assertSame($url, $artist->url());
                $this->assertSame($genres, $artist->genres());
                $this->assertSame($albums, $artist->albums());
                $this->assertSame($artwork, $artist->artwork()->match(
                    static fn($artwork) => $artwork,
                    static fn() => null,
                ));
            });
    }
}
