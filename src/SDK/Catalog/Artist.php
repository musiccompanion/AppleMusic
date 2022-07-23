<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artist\{
    Id,
    Name,
};
use Innmind\Url\Url;
use Innmind\Immutable\Set;
use function Innmind\Immutable\assertSet;

final class Artist
{
    private Id $id;
    private Name $name;
    private Url $url;
    /** @var Set<Genre> */
    private Set $genres;
    /** @var Set<Album\Id> */
    private Set $albums;

    /**
     * @param Set<Genre> $genres
     * @param Set<Album\Id> $albums
     */
    public function __construct(
        Id $id,
        Name $name,
        Url $url,
        Set $genres,
        Set $albums,
    ) {
        assertSet(Genre::class, $genres, 3);
        assertSet(Album\Id::class, $albums, 4);

        $this->id = $id;
        $this->name = $name;
        $this->url = $url;
        $this->genres = $genres;
        $this->albums = $albums;
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
}
