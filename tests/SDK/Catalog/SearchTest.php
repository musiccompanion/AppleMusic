<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Search;
use Fixtures\MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist as ArtistSet,
    Album as AlbumSet,
    Song as SongSet,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set as DataSet,
};
use Fixtures\Innmind\Immutable\Sequence as ISequence;

class SearchTest extends TestCase
{
    use BlackBox;

    public function testInterface(): BlackBox\Proof
    {
        return $this
            ->forAll(
                DataSet\Strings::any(),
                ISequence::of(ArtistSet\Id::any()),
                ISequence::of(AlbumSet\Id::any()),
                ISequence::of(SongSet\Id::any()),
            )
            ->prove(function($term, $artists, $albums, $songs) {
                $search = Search::of($term, $artists, $albums, $songs);

                $this->assertSame($term, $search->term());
                $this->assertSame($artists, $search->artists());
                $this->assertSame($albums, $search->albums());
                $this->assertSame($songs, $search->songs());
            });
    }
}
