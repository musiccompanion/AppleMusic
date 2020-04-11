<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Album,
    Genre,
    Song,
    Artist,
};
use Innmind\Immutable\Set;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\{
    Artwork,
    Genre as GenreSet,
    Album\Id,
    Album\Name,
    Album\RecordLabel,
    Album\Copyright,
    Album\EditorialNotes,
    Song as SongSet,
    Artist as ArtistSet,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};
use Fixtures\Innmind\Immutable\Set as ISet;
use Fixtures\Innmind\Url\Url;
use Fixtures\Innmind\TimeContinuum\Earth\PointInTime;

class AlbumTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Id::any(),
                Artwork::any(),
                Name::any(),
                DataSet\Elements::of(true, false),
                Url::any(),
                DataSet\Elements::of(true, false),
                ISet::of(Genre::class, GenreSet::any()),
                ISet::of(Song\Id::class, SongSet\Id::any()),
                DataSet\Elements::of(true, false),
                PointInTime::any(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any(),
                ISet::of(Artist\Id::class, ArtistSet\Id::any())
            )
            ->then(function($id, $artwork, $name, $single, $url, $complete, $genres, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists) {
                $album = new Album(
                    $id,
                    $artwork,
                    $name,
                    $single,
                    $url,
                    $complete,
                    $genres,
                    $tracks,
                    $masteredForItunes,
                    $release,
                    $recordLabel,
                    $copyright,
                    $editorialNotes,
                    $artists
                );

                $this->assertSame($id, $album->id());
                $this->assertSame($artwork, $album->artwork());
                $this->assertSame($name, $album->name());
                $this->assertSame($single, $album->single());
                $this->assertSame($url, $album->url());
                $this->assertSame($complete, $album->complete());
                $this->assertSame($genres, $album->genres());
                $this->assertSame($tracks, $album->tracks());
                $this->assertSame($masteredForItunes, $album->masteredForItunes());
                $this->assertSame($release, $album->release());
                $this->assertSame($recordLabel, $album->recordLabel());
                $this->assertSame($copyright, $album->copyright());
                $this->assertSame($editorialNotes, $album->editorialNotes());
                $this->assertSame($artists, $album->artists());
            });
    }

    public function testThrowWhenInvalidSetOfGenre()
    {
        $this
            ->forAll(
                Id::any(),
                Artwork::any(),
                Name::any(),
                DataSet\Elements::of(true, false),
                Url::any(),
                DataSet\Elements::of(true, false),
                DataSet\Strings::any()->filter(fn($s) => strpos($s, '?') === false),
                DataSet\Elements::of(true, false),
                PointInTime::any(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any()
            )
            ->disableShrinking()
            ->then(function($id, $artwork, $name, $single, $url, $complete, $genres, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 7 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Genre>');

                new Album(
                    $id,
                    $artwork,
                    $name,
                    $single,
                    $url,
                    $complete,
                    Set::of($genres),
                    Set::of(Song\Id::class),
                    $masteredForItunes,
                    $release,
                    $recordLabel,
                    $copyright,
                    $editorialNotes,
                    Set::of(Artist\Id::class)
                );
            });
    }

    public function testThrowWhenInvalidSetOfTracks()
    {
        $this
            ->forAll(
                Id::any(),
                Artwork::any(),
                Name::any(),
                DataSet\Elements::of(true, false),
                Url::any(),
                DataSet\Elements::of(true, false),
                DataSet\Strings::any()->filter(fn($s) => strpos($s, '?') === false),
                DataSet\Elements::of(true, false),
                PointInTime::any(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any()
            )
            ->disableShrinking()
            ->then(function($id, $artwork, $name, $single, $url, $complete, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 8 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Song\Id>');

                new Album(
                    $id,
                    $artwork,
                    $name,
                    $single,
                    $url,
                    $complete,
                    Set::of(Genre::class),
                    Set::of($tracks),
                    $masteredForItunes,
                    $release,
                    $recordLabel,
                    $copyright,
                    $editorialNotes,
                    Set::of(Artist\Id::class)
                );
            });
    }

    public function testThrowWhenInvalidSetOfArtists()
    {
        $this
            ->forAll(
                Id::any(),
                Artwork::any(),
                Name::any(),
                DataSet\Elements::of(true, false),
                Url::any(),
                DataSet\Elements::of(true, false),
                DataSet\Elements::of(true, false),
                PointInTime::any(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any(),
                DataSet\Strings::any()->filter(fn($s) => strpos($s, '?') === false),
            )
            ->disableShrinking()
            ->then(function($id, $artwork, $name, $single, $url, $complete, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 14 must be of type Set<MusicCompanion\AppleMusic\SDK\Catalog\Artist\Id>');

                new Album(
                    $id,
                    $artwork,
                    $name,
                    $single,
                    $url,
                    $complete,
                    Set::of(Genre::class),
                    Set::of(Song\Id::class),
                    $masteredForItunes,
                    $release,
                    $recordLabel,
                    $copyright,
                    $editorialNotes,
                    Set::of($artists)
                );
            });
    }
}
