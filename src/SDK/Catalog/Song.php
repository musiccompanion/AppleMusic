<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Song\{
    Id,
    DiscNumber,
    Duration,
    Name,
    ISRC,
    TrackNumber,
    Composer,
};
use Innmind\Url\UrlInterface;
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\SetInterface;
use function Innmind\Immutable\assertSet;

final class Song
{
    private $id;
    private $previews;
    private $artwork;
    private $url;
    private $discNumber;
    private $genres;
    private $duration;
    private $release;
    private $name;
    private $isrc;
    private $trackNumber;
    private $composer;
    private $artists;
    private $albums;

    public function __construct(
        Id $id,
        SetInterface $previews,
        Artwork $artwork,
        UrlInterface $url,
        DiscNumber $discNumber,
        SetInterface $genres,
        Duration $duration,
        PointInTimeInterface $release,
        Name $name,
        ISRC $isrc,
        TrackNumber $trackNumber,
        Composer $composer,
        SetInterface $artists,
        SetInterface $albums
    ) {
        assertSet(UrlInterface::class, $previews, 2);
        assertSet(Genre::class, $genres, 6);
        assertSet(Artist\Id::class, $artists, 13);
        assertSet(Album\Id::class, $albums, 14);

        $this->id = $id;
        $this->previews = $previews;
        $this->artwork = $artwork;
        $this->url = $url;
        $this->discNumber = $discNumber;
        $this->genres = $genres;
        $this->duration = $duration;
        $this->release = $release;
        $this->name = $name;
        $this->isrc = $isrc;
        $this->trackNumber = $trackNumber;
        $this->composer = $composer;
        $this->artists = $artists;
        $this->albums = $albums;
    }

    public function id(): Id
    {
        return $this->id;
    }

    /**
     * @return SetInterface<UrlInterface>
     */
    public function previews(): SetInterface
    {
        return $this->previews;
    }

    public function artwork(): Artwork
    {
        return $this->artwork;
    }

    public function url(): UrlInterface
    {
        return $this->url;
    }

    public function discNumber(): DiscNumber
    {
        return $this->discNumber;
    }

    /**
     * @return SetInterface<Genre>
     */
    public function genres(): SetInterface
    {
        return $this->genres;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }

    public function release(): PointInTimeInterface
    {
        return $this->release;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function isrc(): ISRC
    {
        return $this->isrc;
    }

    public function trackNumber(): TrackNumber
    {
        return $this->trackNumber;
    }

    public function composer(): Composer
    {
        return $this->composer;
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
}
