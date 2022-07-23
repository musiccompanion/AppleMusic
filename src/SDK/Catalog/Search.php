<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use Innmind\Immutable\Sequence;
use function Innmind\Immutable\assertSequence;

final class Search
{
    private string $term;
    /** @var Sequence<Artist\Id> */
    private Sequence $artists;
    /** @var Sequence<Album\Id> */
    private Sequence $albums;
    /** @var Sequence<Song\Id> */
    private Sequence $songs;

    /**
     * @param Sequence<Artist\Id> $artists
     * @param Sequence<Album\Id> $albums
     * @param Sequence<Song\Id> $songs
     */
    public function __construct(
        string $term,
        Sequence $artists,
        Sequence $albums,
        Sequence $songs,
    ) {
        assertSequence(Artist\Id::class, $artists, 2);
        assertSequence(Album\Id::class, $albums, 3);
        assertSequence(Song\Id::class, $songs, 4);

        $this->term = $term;
        $this->artists = $artists;
        $this->albums = $albums;
        $this->songs = $songs;
    }

    public function term(): string
    {
        return $this->term;
    }

    /**
     * @return Sequence<Artist\Id>
     */
    public function artists(): Sequence
    {
        return $this->artists;
    }

    /**
     * @return Sequence<Album\Id>
     */
    public function albums(): Sequence
    {
        return $this->albums;
    }

    /**
     * @return Sequence<Song\Id>
     */
    public function songs(): Sequence
    {
        return $this->songs;
    }
}
