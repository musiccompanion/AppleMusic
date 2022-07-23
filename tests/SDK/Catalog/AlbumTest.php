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
                ISet::of(GenreSet::any()),
                ISet::of(SongSet\Id::any()),
                DataSet\Elements::of(true, false),
                PointInTime::any(),
                RecordLabel::any(),
                Copyright::any(),
                EditorialNotes::any(),
                ISet::of(ArtistSet\Id::any()),
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
                    $artists,
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
}
