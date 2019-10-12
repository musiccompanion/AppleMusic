<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\{
    Id,
    Artwork,
    Name,
    RecordLabel,
    Copyright,
    EditorialNotes,
};
use Innmind\Url\UrlInterface;
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\SetInterface;
use function Innmind\Immutable\assertSet;

final class Album
{
    private $id;
    private $artwork;
    private $name;
    private $single;
    private $url;
    private $complete;
    private $genres;
    private $tracks;
    private $masteredForItunes;
    private $release;
    private $recordLabel;
    private $copyright;
    private $editorialNotes;
    private $artists;

    public function __construct(
        Id $id,
        Artwork $artwork,
        Name $name,
        bool $single,
        UrlInterface $url,
        bool $complete,
        SetInterface $genres,
        SetInterface $tracks,
        bool $masteredForItunes,
        PointInTimeInterface $release,
        RecordLabel $recordLabel,
        Copyright $copyright,
        EditorialNotes $editorialNotes,
        SetInterface $artists
    ) {
        assertSet(Genre::class, $genres, 7);
        assertSet(Song\Id::class, $tracks, 8);
        assertSet(Artist\Id::class, $artists, 14);

        $this->id = $id;
        $this->artwork = $artwork;
        $this->name = $name;
        $this->single = $single;
        $this->url = $url;
        $this->complete = $complete;
        $this->genres = $genres;
        $this->tracks = $tracks;
        $this->masteredForItunes = $masteredForItunes;
        $this->release = $release;
        $this->recordLabel = $recordLabel;
        $this->copyright = $copyright;
        $this->editorialNotes = $editorialNotes;
        $this->artists = $artists;
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function artwork(): Artwork
    {
        return $this->artwork;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function single(): bool
    {
        return $this->single;
    }

    public function url(): UrlInterface
    {
        return $this->url;
    }

    public function complete(): bool
    {
        return $this->complete;
    }

    /**
     * @return SetInterface<Genre>
     */
    public function genres(): SetInterface
    {
        return $this->genres;
    }

    /**
     * @return SetInterface<Song\Id>
     */
    public function tracks(): SetInterface
    {
        return $this->tracks;
    }

    public function masteredForItunes(): bool
    {
        return $this->masteredForItunes;
    }

    public function release(): PointInTimeInterface
    {
        return $this->release;
    }

    public function recordLabel(): RecordLabel
    {
        return $this->recordLabel;
    }

    public function copyright(): Copyright
    {
        return $this->copyright;
    }

    public function editorialNotes(): EditorialNotes
    {
        return $this->editorialNotes;
    }

    /**
     * @return SetInterface<Artist\Id>
     */
    public function artists(): SetInterface
    {
        return $this->artists;
    }
}
