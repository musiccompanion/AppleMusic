<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artist\{
    Id,
    Name,
};
use Innmind\Url\Url;
use Innmind\Immutable\{
    Set,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Artist
{
    private Id $id;
    private Name $name;
    private Url $url;
    /** @var Set<Genre> */
    private Set $genres;
    /** @var Set<Album\Id> */
    private Set $albums;
    /** @var Maybe<Artwork> */
    private Maybe $artwork;

    /**
     * @param Set<Genre> $genres
     * @param Set<Album\Id> $albums
     * @param Maybe<Artwork> $artwork
     */
    private function __construct(
        Id $id,
        Name $name,
        Url $url,
        Set $genres,
        Set $albums,
        Maybe $artwork,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->genres = $genres;
        $this->albums = $albums;
        $this->artwork = $artwork;
    }

    /**
     * @psalm-pure
     *
     * @param Set<Genre> $genres
     * @param Set<Album\Id> $albums
     * @param Maybe<Artwork> $artwork
     */
    public static function of(
        Id $id,
        Name $name,
        Url $url,
        Set $genres,
        Set $albums,
        Maybe $artwork,
    ): self {
        return new self(
            $id,
            $name,
            $url,
            $genres,
            $albums,
            $artwork,
        );
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function url(): Url
    {
        return $this->url;
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
     * @return Maybe<Artwork>
     */
    public function artwork(): Maybe
    {
        return $this->artwork;
    }
}
