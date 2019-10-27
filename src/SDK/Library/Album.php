<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Album\{
    Id,
    Name,
    Artwork,
};
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Album
{
    private $id;
    private $name;
    private $artwork;
    private $artists;

    public function __construct(
        Id $id,
        Name $name,
        ?Artwork $artwork,
        Artist\Id ...$artists
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->artwork = $artwork;
        $this->artists = Set::of(Artist\Id::class, ...$artists);
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function hasArtwork(): bool
    {
        return $this->artwork instanceof Artwork;
    }

    public function artwork(): Artwork
    {
        return $this->artwork;
    }

    /**
     * @return SetInterface<Artist\Id>
     */
    public function artists(): SetInterface
    {
        return $this->artists;
    }
}
