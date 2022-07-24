<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Album\{
    Id,
    Name,
    Artwork,
};
use Innmind\Immutable\{
    Set,
    Maybe,
};

final class Album
{
    private Id $id;
    private Name $name;
    /** @var Maybe<Artwork> */
    private Maybe $artwork;
    /** @var Set<Artist\Id> */
    private Set $artists;

    /**
     * @param Maybe<Artwork> $artwork
     * @param Set<Artist\Id> $artists
     */
    public function __construct(
        Id $id,
        Name $name,
        Maybe $artwork,
        Set $artists,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->artwork = $artwork;
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
     * @return Maybe<Artwork>
     */
    public function artwork(): Maybe
    {
        return $this->artwork;
    }

    /**
     * @return Set<Artist\Id>
     */
    public function artists(): Set
    {
        return $this->artists;
    }
}
