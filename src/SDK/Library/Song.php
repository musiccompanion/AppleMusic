<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Song\{
    Id,
    Name,
    Genre,
    Duration,
    TrackNumber,
};
use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Song
{
    private Id $id;
    private Name $name;
    private Duration $duration;
    private TrackNumber $trackNumber;
    private Set $genres;
    private Set $albums;
    private Set $artists;

    public function __construct(
        Id $id,
        Name $name,
        Duration $duration,
        TrackNumber $trackNumber,
        Set $genres,
        Set $albums,
        Set $artists
    ) {
        assertSet(Genre::class, $genres, 5);
        assertSet(Album\Id::class, $albums, 6);
        assertSet(Artist\Id::class, $artists, 7);

        $this->id = $id;
        $this->name = $name;
        $this->duration = $duration;
        $this->trackNumber = $trackNumber;
        $this->genres = $genres;
        $this->albums = $albums;
        $this->artists = $artists;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }

    public function trackNumber(): TrackNumber
    {
        return $this->trackNumber;
    }

    /**
     * @return Set<Genre>
     */
    public function genres(): Set
    {
        return $this->genres;
    }

    /**
     * @return Set<Album\Id>
     */
    public function albums(): Set
    {
        return $this->albums;
    }

    /**
     * @return Set<Artist\Id>
     */
    public function artists(): Set
    {
        return $this->artists;
    }
}
