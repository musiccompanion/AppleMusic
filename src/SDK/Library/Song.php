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
use Innmind\Immutable\{
    Set,
    Maybe,
};

final class Song
{
    private Id $id;
    private Name $name;
    /** @var Maybe<Duration> */
    private Maybe $duration;
    private TrackNumber $trackNumber;
    /** @var Set<Genre> */
    private Set $genres;
    /** @var Set<Album\Id> */
    private Set $albums;
    /** @var Set<Artist\Id> */
    private Set $artists;

    /**
     * @param Maybe<Duration> $duration
     * @param Set<Genre> $genres
     * @param Set<Album\Id> $albums
     * @param Set<Artist\Id> $artists
     */
    public function __construct(
        Id $id,
        Name $name,
        Maybe $duration,
        TrackNumber $trackNumber,
        Set $genres,
        Set $albums,
        Set $artists,
    ) {
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

    /**
     * @return Maybe<Duration>
     */
    public function duration(): Maybe
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
