<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Library\{
    Artist,
    Album,
    Song,
};
use Innmind\Immutable\{
    Set,
    Sequence,
};

interface Library
{
    public function storefront(): Storefront;

    /**
     * @return Sequence<Artist>
     */
    public function artists(): Sequence;

    /**
     * @return Set<Album>
     */
    public function albums(Artist\Id $artist): Set;

    /**
     * @return Set<Song>
     */
    public function songs(Album\Id $album): Set;
}
