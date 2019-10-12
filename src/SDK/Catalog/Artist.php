<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artist\{
    Id,
    Name,
    Genre,
};
use Innmind\Url\UrlInterface;
use Innmind\Immutable\SetInterface;
use function Innmind\Immutable\assertSet;

final class Artist
{
    private $id;
    private $name;
    private $url;
    private $genres;
    private $albums;

    public function __construct(
        Id $id,
        Name $name,
        UrlInterface $url,
        SetInterface $genres,
        SetInterface $albums
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

    public function url(): UrlInterface
    {
        return $this->url;
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
}
