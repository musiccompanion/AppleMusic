<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist,
    Album,
    Genre,
    Song,
    Artwork,
    Search,
};
use Innmind\Immutable\{
    Set,
    Sequence,
};

interface Catalog
{
    public function artist(Artist\Id $id): Artist;
    public function album(Album\Id $id): Album;
    public function song(Song\Id $id): Song;

    /**
     * @return Set<Genre>
     */
    public function genres(): Set;
    public function search(string $term): Search;
}
