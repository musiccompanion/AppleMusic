<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Song;
use Innmind\Immutable\Maybe;
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
use Innmind\BlackBox\PHPUnit\BlackBox;
use Fixtures\Innmind\Immutable\Set as ISet;

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
                ISet::of(Genre::any()),
                ISet::of(AlbumSet\Id::any()),
                ISet::of(ArtistSet\Id::any()),
            )
            ->then(function($id, $name, $duration, $trackNumber, $genres, $albums, $artists) {
                $song = Song::of(
                    $id,
                    $name,
                    Maybe::of($duration),
                    Maybe::of($trackNumber),
                    $genres,
                    $albums,
                    $artists,
                );

                $this->assertSame($id, $song->id());
                $this->assertSame($name, $song->name());
                $this->assertSame($duration, $song->duration()->match(
                    static fn($duration) => $duration,
                    static fn() => null,
                ));
                $this->assertSame($genres, $song->genres());
                $this->assertSame($albums, $song->albums());
                $this->assertSame($artists, $song->artists());
            });
    }
}
