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
    SetInterface,
    Set,
};
use function Innmind\Immutable\assertSet;

final class Song
{
    private $id;
    private $name;
    private $duration;
    private $trackNumber;
    private $genres;
    private $albums;
    private $artists;

    public function __construct(
        Id $id,
        Name $name,
        Duration $duration,
        TrackNumber $trackNumber,
        SetInterface $genres,
        SetInterface $albums,
        SetInterface $artists
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
     * @return SetInterface<Genre>
     */
    public function genres(): SetInterface
    {
        return $this->genres;
    }

    /**
     * @return SetInterface<Album\Id>
     */
    public function albums(): SetInterface
    {
        return $this->albums;
    }

    /**
     * @return SetInterface<Artist\Id>
     */
    public function artists(): SetInterface
    {
        return $this->artists;
    }
}
