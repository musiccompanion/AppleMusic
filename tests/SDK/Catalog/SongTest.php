<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Song,
    Artist,
    Album,
    Genre,
};
use Innmind\Immutable\{
    Set,
    Maybe,
};
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
use Fixtures\Innmind\Immutable\Set as ISet;
use Fixtures\Innmind\Url\Url as FUrl;
use Fixtures\Innmind\TimeContinuum\Earth\PointInTime;

class SongTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                ISet::of(FUrl::any()),
                Artwork::any(),
                FUrl::any(),
                DiscNumber::any(),
                ISet::of(GenreSet::any()),
                Duration::any(),
                PointInTime::any(),
                Name::any(),
                ISRC::any(),
                TrackNumber::any(),
                Composer::any(),
                ISet::of(ArtistSet\Id::any()),
                ISet::of(AlbumSet\Id::any()),
            )
            ->then(function($id, $previews, $artwork, $url, $discNumber, $genres, $duration, $release, $name, $isrc, $trackNumber, $composer, $artists, $albums) {
                $song = new Song(
                    $id,
                    $previews,
                    $artwork,
                    $url,
                    $discNumber,
                    $genres,
                    Maybe::of($duration),
                    $release,
                    $name,
                    $isrc,
                    $trackNumber,
                    $composer,
                    $artists,
                    $albums,
                );

                $this->assertSame($id, $song->id());
                $this->assertSame($previews, $song->previews());
                $this->assertSame($artwork, $song->artwork());
                $this->assertSame($url, $song->url());
                $this->assertSame($discNumber, $song->discNumber());
                $this->assertSame($genres, $song->genres());
                $this->assertSame($duration, $song->duration()->match(
                    static fn($duration) => $duration,
                    static fn() => null,
                ));
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
