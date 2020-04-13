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
use Innmind\Url\Url;
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Song
{
    private Id $id;
    /** @var Set<Url> */
    private Set $previews;
    private Artwork $artwork;
    private Url $url;
    private DiscNumber $discNumber;
    /** @var Set<Genre> */
    private Set $genres;
    private ?Duration $duration;
    private PointInTime $release;
    private Name $name;
    private ISRC $isrc;
    private TrackNumber $trackNumber;
    private Composer $composer;
    /** @var Set<Artist\Id> */
    private Set $artists;
    /** @var Set<Album\Id> */
    private Set $albums;

    /**
     * @param Set<Url> $previews
     * @param Set<Genre> $genres
     * @param Set<Artist\Id> $artists
     * @param Set<Album\Id> $albums
     */
    public function __construct(
        Id $id,
        Set $previews,
        Artwork $artwork,
        Url $url,
        DiscNumber $discNumber,
        Set $genres,
        ?Duration $duration,
        PointInTime $release,
        Name $name,
        ISRC $isrc,
        TrackNumber $trackNumber,
        Composer $composer,
        Set $artists,
        Set $albums
    ) {
        assertSet(Url::class, $previews, 2);
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
     * @return Set<Url>
     */
    public function previews(): Set
    {
        return $this->previews;
    }

    public function artwork(): Artwork
    {
        return $this->artwork;
    }

    public function url(): Url
    {
        return $this->url;
    }

    public function discNumber(): DiscNumber
    {
        return $this->discNumber;
    }

    /**
     * @return Set<Genre>
     */
    public function genres(): Set
    {
        return $this->genres;
    }

    public function durationKnown(): bool
    {
        return $this->duration instanceof Duration;
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function duration(): Duration
    {
        /** @psalm-suppress NullableReturnStatement */
        return $this->duration;
    }

    public function release(): PointInTime
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
}
