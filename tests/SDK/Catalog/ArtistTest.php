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

class ArtistTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                DataSet\Url::of(),
                DataSet\Set::of(Genre::class, GenreSet::any()),
                DataSet\Set::of(Album\Id::class, AlbumSet\Id::any())
            )
            ->take(1000)
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
                DataSet\Url::of(),
                DataSet\Strings::of()->filter(fn($s) => strpos($s, '?') === false),
            )
            ->take(1000)
            ->then(function($id, $name, $url, string $type) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 3 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Genre>');

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
                DataSet\Url::of(),
                DataSet\Strings::of()->filter(fn($s) => strpos($s, '?') === false),
            )
            ->take(1000)
            ->then(function($id, $name, $url, string $type) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 4 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Album\Id>');

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
