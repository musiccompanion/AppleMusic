<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use Innmind\Immutable\SetInterface;
use function Innmind\Immutable\assertSet;

final class Search
{
    private string $term;
    private SetInterface $artists;
    private SetInterface $albums;
    private SetInterface $songs;

    public function __construct(
        string $term,
        SetInterface $artists,
        SetInterface $albums,
        SetInterface $songs
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
     * @return SetInterface<Artist\Id>
     */
    public function artists(): SetInterface
    {
        return $this->artists;
    }

    /**
     * @return SetInterface<Album\Id>
     */
    public function albums(): SetInterface
    {
        return $this->albums;
    }

    /**
     * @return SetInterface<Song\Id>
     */
    public function songs(): SetInterface
    {
        return $this->songs;
    }
}
