<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library;

use MusicCompanion\AppleMusic\SDK\Library\Album\{
    Id,
    Name,
    Artwork,
};
use Innmind\Immutable\Set;

final class Album
{
    private Id $id;
    private Name $name;
    private ?Artwork $artwork;
    /** @var Set<Artist\Id> */
    private Set $artists;

    /**
     * @param Set<Artist\Id> $artists
     */
    public function __construct(
        Id $id,
        Name $name,
        ?Artwork $artwork,
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

    public function hasArtwork(): bool
    {
        return $this->artwork instanceof Artwork;
    }

    /** @psalm-suppress InvalidNullableReturnType */
    public function artwork(): Artwork
    {
        /** @psalm-suppress NullableReturnStatement */
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
