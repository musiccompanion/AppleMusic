<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\{
    Song,
    Album,
    Artist,
};
use Innmind\Immutable\Set;
use Fixtures\MusicCompanion\AppleMusic\SDK\Library\{
    Song\Id,
    Song\Name,
    Song\Genre,
    Song\Duration,
    Song\TrackNumber,
    Album as AlbumSet,
    Artist as ArtistSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};

class SongTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Duration::any(),
                TrackNumber::any(),
                DataSet\Set::of(Song\Genre::class, Genre::any()),
                DataSet\Set::of(Album\Id::class, AlbumSet\Id::any()),
                DataSet\Set::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->take(1000)
            ->then(function($id, $name, $duration, $trackNumber, $genres, $albums, $artists) {
                $song = new Song(
                    $id,
                    $name,
                    $duration,
                    $trackNumber,
                    $genres,
                    $albums,
                    $artists
                );

                $this->assertSame($id, $song->id());
                $this->assertSame($name, $song->name());
                $this->assertSame($duration, $song->duration());
                $this->assertSame($genres, $song->genres());
                $this->assertSame($albums, $song->albums());
                $this->assertSame($artists, $song->artists());
            });
    }

    public function testThrowWhenInvalidSetOfGenre()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Duration::any(),
                TrackNumber::any(),
                DataSet\Strings::of()->filter(fn($s) => strpos($s, '?') === false),
                DataSet\Set::of(Album\Id::class, AlbumSet\Id::any()),
                DataSet\Set::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->take(1000)
            ->then(function($id, $name, $duration, $trackNumber, $genre, $albums, $artists) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 5 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Library\Song\Genre>');

                new Song(
                    $id,
                    $name,
                    $duration,
                    $trackNumber,
                    Set::of($genre),
                    $albums,
                    $artists
                );
            });
    }

    public function testThrowWhenInvalidSetOfAlbum()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Duration::any(),
                TrackNumber::any(),
                DataSet\Set::of(Song\Genre::class, Genre::any()),
                DataSet\Strings::of()->filter(fn($s) => strpos($s, '?') === false),
                DataSet\Set::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->take(1000)
            ->then(function($id, $name, $duration, $trackNumber, $genres, $album, $artists) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 6 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Library\Album\Id>');

                new Song(
                    $id,
                    $name,
                    $duration,
                    $trackNumber,
                    $genres,
                    Set::of($album),
                    $artists
                );
            });
    }

    public function testThrowWhenInvalidSetOfArtist()
    {
        $this
            ->forAll(
                Id::any(),
                Name::any(),
                Duration::any(),
                TrackNumber::any(),
                DataSet\Set::of(Song\Genre::class, Genre::any()),
                DataSet\Set::of(Album\Id::class, AlbumSet\Id::any()),
                DataSet\Strings::of()->filter(fn($s) => strpos($s, '?') === false),
            )
            ->take(1000)
            ->then(function($id, $name, $duration, $trackNumber, $genres, $albums, $artist) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 7 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Library\Artist\Id>');

                new Song(
                    $id,
                    $name,
                    $duration,
                    $trackNumber,
                    $genres,
                    $albums,
                    Set::of($artist)
                );
            });
    }
}
