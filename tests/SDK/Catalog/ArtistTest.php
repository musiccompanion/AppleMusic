<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist,
    Album,
    Genre,
};
use Innmind\Immutable\Set;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist\Id,
    Artist\Name,
    Genre as GenreSet,
    Album as AlbumSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};
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
                ISet::of(Genre::class, GenreSet::any()),
                ISet::of(Album\Id::class, AlbumSet\Id::any())
            )
            ->then(function($id, $name, $url, $genres, $albums) {
                $artist = new Artist(
                    $id,
                    $name,
                    $url,
                    $genres,
                    $albums
                );

                $this->assertSame($id, $artist->id());
                $this->assertSame($name, $artist->name());
                $this->assertSame($url, $artist->url());
                $this->assertSame($genres, $artist->genres());
                $this->assertSame($albums, $artist->albums());
            });
    }

    public function testThrowWhenInvalidSetOfGenres()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Url::any(),
                DataSet\Strings::any()->filter(static fn($s) => \strpos($s, '?') === false),
            )
            ->disableShrinking()
            ->then(function($id, $name, $url, string $type) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 3 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Genre>');

                new Artist(
                    $id,
                    $name,
                    $url,
                    Set::of($type),
                    Set::of(Album\Id::class)
                );
            });
    }

    public function testThrowWhenInvalidSetOfAlbums()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Url::any(),
                DataSet\Strings::any()->filter(static fn($s) => \strpos($s, '?') === false),
            )
            ->disableShrinking()
            ->then(function($id, $name, $url, string $type) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 4 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Album\Id>');

                new Artist(
                    $id,
                    $name,
                    $url,
                    Set::of(Genre::class),
                    Set::of($type)
                );
            });
    }
}
