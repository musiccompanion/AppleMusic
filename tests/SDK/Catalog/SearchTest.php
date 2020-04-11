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
use Fixtures\Innmind\Immutable\Set as ISet;

class SearchTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                DataSet\Strings::any(),
                ISet::of(Artist\Id::class, ArtistSet\Id::any()),
                ISet::of(Album\Id::class, AlbumSet\Id::any()),
                ISet::of(Song\Id::class, SongSet\Id::any())
            )
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
                DataSet\Strings::any(),
                DataSet\Strings::any()->filter(fn($s) => strpos($s, '?') === false),
                ISet::of(Album\Id::class, AlbumSet\Id::any()),
                ISet::of(Song\Id::class, SongSet\Id::any())
            )
            ->disableShrinking()
            ->then(function($term, $artist, $albums, $songs) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 2 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Artist\Id>');

                new Search(
                    $term,
                    Set::of($artist),
                    $albums,
                    $songs
                );
            });
    }

    public function testThrowWhenInvalidAlbumSet()
    {
        $this
            ->forAll(
                DataSet\Strings::any(),
                ISet::of(Artist\Id::class, ArtistSet\Id::any()),
                DataSet\Strings::any()->filter(fn($s) => strpos($s, '?') === false),
                ISet::of(Song\Id::class, SongSet\Id::any())
            )
            ->disableShrinking()
            ->then(function($term, $artists, $album, $songs) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 3 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Album\Id>');

                new Search(
                    $term,
                    $artists,
                    Set::of($album),
                    $songs
                );
            });
    }

    public function testThrowWhenInvalidSongSet()
    {
        $this
            ->forAll(
                DataSet\Strings::any(),
                ISet::of(Artist\Id::class, ArtistSet\Id::any()),
                ISet::of(Album\Id::class, AlbumSet\Id::any()),
                DataSet\Strings::any()->filter(fn($s) => strpos($s, '?') === false),
            )
            ->disableShrinking()
            ->then(function($term, $artists, $albums, $song) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 4 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Song\Id>');

                new Search(
                    $term,
                    $artists,
                    $albums,
                    Set::of($song)
                );
            });
    }
}
