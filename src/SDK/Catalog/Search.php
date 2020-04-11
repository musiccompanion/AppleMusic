<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Search
{
    private string $term;
    private Set $artists;
    private Set $albums;
    private Set $songs;

    public function __construct(
        string $term,
        Set $artists,
        Set $albums,
        Set $songs
    ) {
        assertSet(Artist\Id::class, $artists, 2);
        assertSet(Album\Id::class, $albums, 3);
        assertSet(Song\Id::class, $songs, 4);

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
     * @return Set<Artist\Id>
     */
    public function artists(): Set
    {
        return $this->artists;
    }

    /**
     * @return Set<Album\Id>
     */
    public function albums(): Set
    {
        return $this->albums;
    }

    /**
     * @return Set<Song\Id>
     */
    public function songs(): Set
    {
        return $this->songs;
    }
}
