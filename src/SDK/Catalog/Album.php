<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Album\{
    Id,
    Name,
    RecordLabel,
    Copyright,
    EditorialNotes,
};
use Innmind\Url\Url;
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Set,
    Maybe,
};

final class Album
{
    private Id $id;
    /** @var Maybe<Artwork> */
    private Maybe $artwork;
    private Name $name;
    private bool $single;
    private Url $url;
    private bool $complete;
    /** @var Set<Genre> */
    private Set $genres;
    /** @var Set<Song\Id> */
    private Set $tracks;
    private bool $masteredForItunes;
    /** @var Maybe<PointInTime> */
    private Maybe $release;
    private RecordLabel $recordLabel;
    private Copyright $copyright;
    private EditorialNotes $editorialNotes;
    /** @var Set<Artist\Id> */
    private Set $artists;

    /**
     * @param Maybe<Artwork> $artwork
     * @param Set<Genre> $genres
     * @param Set<Song\Id> $tracks
     * @param Maybe<PointInTime> $release
     * @param Set<Artist\Id> $artists
     */
    public function __construct(
        Id $id,
        Maybe $artwork,
        Name $name,
        bool $single,
        Url $url,
        bool $complete,
        Set $genres,
        Set $tracks,
        bool $masteredForItunes,
        Maybe $release,
        RecordLabel $recordLabel,
        Copyright $copyright,
        EditorialNotes $editorialNotes,
        Set $artists,
    ) {
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

    /**
     * @return Maybe<Artwork>
     */
    public function artwork(): Maybe
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

    public function url(): Url
    {
        return $this->url;
    }

    public function complete(): bool
    {
        return $this->complete;
    }

    /**
     * @return Set<Genre>
     */
    public function genres(): Set
    {
        return $this->genres;
    }

    /**
     * @return Set<Song\Id>
     */
    public function tracks(): Set
    {
        return $this->tracks;
    }

    public function masteredForItunes(): bool
    {
        return $this->masteredForItunes;
    }

    /**
     * @return Maybe<PointInTime>
     */
    public function release(): Maybe
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
     * @return Set<Artist\Id>
     */
    public function artists(): Set
    {
        return $this->artists;
    }
}
