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
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\Album\{
    Id,
    Artwork,
    Name,
    RecordLabel,
    Copyright,
    EditorialNotes,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};

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
                DataSet\Url::of(),
                DataSet\Elements::of(true, false),
                DataSet\Elements::of(true, false),
                DataSet\PointInTime::of(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any()
            )
            ->then(function($id, $artwork, $name, $single, $url, $complete, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes) {
                $album = new Album(
                    $id,
                    $artwork,
                    $name,
                    $single,
                    $url,
                    $complete,
                    $genres = Set::of(Genre::class),
                    $tracks = Set::of(Song\Id::class),
                    $masteredForItunes,
                    $release,
                    $recordLabel,
                    $copyright,
                    $editorialNotes,
                    $artists = Set::of(Artist\Id::class)
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
                DataSet\Url::of(),
                DataSet\Elements::of(true, false),
                new DataSet\Strings,
                DataSet\Elements::of(true, false),
                DataSet\PointInTime::of(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any()
            )
            ->then(function($id, $artwork, $name, $single, $url, $complete, $genres, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 7 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Genre>');

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
                DataSet\Url::of(),
                DataSet\Elements::of(true, false),
                new DataSet\Strings,
                DataSet\Elements::of(true, false),
                DataSet\PointInTime::of(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any()
            )
            ->then(function($id, $artwork, $name, $single, $url, $complete, $tracks, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 8 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Song\Id>');

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
                DataSet\Url::of(),
                DataSet\Elements::of(true, false),
                DataSet\Elements::of(true, false),
                DataSet\PointInTime::of(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any(),
                new DataSet\Strings
            )
            ->then(function($id, $artwork, $name, $single, $url, $complete, $masteredForItunes, $release, $recordLabel, $copyright, $editorialNotes, $artists) {
                $this->expectException(\TypeError::class);
                $this->expectExceptionMessage('Argument 14 must be of type SetInterface<MusicCompanion\AppleMusic\SDK\Catalog\Artist\Id>');

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