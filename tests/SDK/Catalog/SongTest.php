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
    Genre as GenreSet,
    Artist as ArtistSet,
    Album as AlbumSet,
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
                DataSet\Set::of(UrlInterface::class, DataSet\Url::of()),
                Artwork::any(),
                DataSet\Url::of(),
                DiscNumber::any(),
                DataSet\Set::of(Genre::class, GenreSet::any()),
                Duration::any(),
                DataSet\PointInTime::of(),
                Name::any(),
                ISRC::any(),
                TrackNumber::any(),
                Composer::any(),
                DataSet\Set::of(Artist\Id::class, ArtistSet\Id::any()),
                DataSet\Set::of(Album\Id::class, AlbumSet\Id::any())
            )
            ->take(1000)
            ->then(function($id, $previews, $artwork, $url, $discNumber, $genres, $duration, $release, $name, $isrc, $trackNumber, $composer, $artists, $albums) {
                $song = new Song(
                    $id,
                    $previews,
                    $artwork,
                    $url,
                    $discNumber,
                    $genres,
                    $duration,
                    $release,
                    $name,
                    $isrc,
                    $trackNumber,
                    $composer,
                    $artists,
                    $albums
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
