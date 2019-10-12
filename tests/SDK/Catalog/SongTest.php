<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Song,
    Artist,
    Album,
    Genre,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\Set;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\{
    Artwork,
    Song\Id,
    Song\DiscNumber,
    Song\Duration,
    Song\Name,
    Song\ISRC,
    Song\TrackNumber,
    Song\Composer,
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
                Artwork::any(),
                DataSet\Url::of(),
                DiscNumber::any(),
                Duration::any(),
                DataSet\PointInTime::of(),
                Name::any(),
                ISRC::any(),
                TrackNumber::any(),
                Composer::any()
            )
            ->take(1000)
            ->then(function($id, $artwork, $url, $discNumber, $duration, $release, $name, $isrc, $trackNumber, $composer) {
                $song = new Song(
                    $id,
                    $previews = Set::of(UrlInterface::class),
                    $artwork,
                    $url,
                    $discNumber,
                    $genres = Set::of(Genre::class),
                    $duration,
                    $release,
                    $name,
                    $isrc,
                    $trackNumber,
                    $composer,
                    $artists = Set::of(Artist\Id::class),
                    $albums = Set::of(Album\Id::class)
                );

                $this->assertSame($id, $song->id());
                $this->assertSame($previews, $song->previews());
                $this->assertSame($artwork, $song->artwork());
                $this->assertSame($url, $song->url());
                $this->assertSame($discNumber, $song->discNumber());
                $this->assertSame($genres, $song->genres());
                $this->assertSame($duration, $song->duration());
                $this->assertSame($release, $song->release());
                $this->assertSame($name, $song->name());
                $this->assertSame($isrc, $song->isrc());
                $this->assertSame($trackNumber, $song->trackNumber());
                $this->assertSame($composer, $song->composer());
                $this->assertSame($artists, $song->artists());
                $this->assertSame($albums, $song->albums());
            });
    }
}
