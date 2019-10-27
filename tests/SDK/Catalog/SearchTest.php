<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Search,
    Artist,
    Album,
    Song,
};
use Innmind\Immutable\Set;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist as ArtistSet,
    Album as AlbumSet,
    Song as SongSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};

class SearchTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                new DataSet\Strings,
                new DataSet\Set(Artist\Id::class, ArtistSet\Id::any()),
                new DataSet\Set(Album\Id::class, AlbumSet\Id::any()),
                new DataSet\Set(Song\Id::class, SongSet\Id::any())
            )
            ->take(100)
            ->then(function($term, $artists, $albums, $songs) {
                $search = new Search($term, $artists, $albums, $songs);

                $this->assertSame($term, $search->term());
                $this->assertSame($artists, $search->artists());
                $this->assertSame($albums, $search->albums());
                $this->assertSame($songs, $search->songs());
            });
    }

    public function testThrowWhenInvalidArtistSet()
    {
        $this
            ->forAll(
                new DataSet\Strings,
                new DataSet\Strings,
                new DataSet\Set(Album\Id::class, AlbumSet\Id::any()),
                new DataSet\Set(Song\Id::class, SongSet\Id::any())
            )
            ->take(100)
            ->then(function($term, $artist, $albums, $songs) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 2 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Artist\Id>');

                new Search(
                    $term,
                    new Set($artist),
                    $albums,
                    $songs
                );
            });
    }

    public function testThrowWhenInvalidAlbumSet()
    {
        $this
            ->forAll(
                new DataSet\Strings,
                new DataSet\Set(Artist\Id::class, ArtistSet\Id::any()),
                new DataSet\Strings,
                new DataSet\Set(Song\Id::class, SongSet\Id::any())
            )
            ->take(100)
            ->then(function($term, $artists, $album, $songs) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 3 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Album\Id>');

                new Search(
                    $term,
                    $artists,
                    new Set($album),
                    $songs
                );
            });
    }

    public function testThrowWhenInvalidSongSet()
    {
        $this
            ->forAll(
                new DataSet\Strings,
                new DataSet\Set(Artist\Id::class, ArtistSet\Id::any()),
                new DataSet\Set(Album\Id::class, AlbumSet\Id::any()),
                new DataSet\Strings
            )
            ->take(100)
            ->then(function($term, $artists, $albums, $song) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 4 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Song\Id>');

                new Search(
                    $term,
                    $artists,
                    $albums,
                    new Set($song)
                );
            });
    }
}
