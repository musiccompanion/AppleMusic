<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\{
    Catalog as CatalogInterface,
    Catalog\Artist,
    Catalog\Album,
    Catalog\Genre,
    Catalog\Song,
    Catalog\Artwork,
    Catalog\Search,
    Storefront,
};
use Innmind\TimeContinuum\Clock;
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header\Authorization,
    Message\Request\Request,
    Message\Method,
    ProtocolVersion,
    Headers,
};
use Innmind\Url\Url;
use Innmind\Colour\RGBA;
use Innmind\Json\Json;
use Innmind\Immutable\{
    Set,
    Sequence,
};

final class Catalog implements CatalogInterface
{
    private Clock $clock;
    private Transport $fulfill;
    private Authorization $authorization;
    private Storefront\Id $storefront;

    public function __construct(
        Clock $clock,
        Transport $fulfill,
        Authorization $authorization,
        Storefront\Id $storefront
    ) {
        $this->clock = $clock;
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
        $this->storefront = $storefront;
    }

    public function artist(Artist\Id $id): Artist
    {
        /** @var array{data: array{0: array{attributes: array{name: string, url: string, genreNames: list<string>}, relationships: array{albums: array{data: list<array{id: int}>, next?: string}}}}} */
        $resource = $this->get($this->url("artists/$id"));

        return new Artist(
            $id,
            new Artist\Name($resource['data'][0]['attributes']['name']),
            Url::of($resource['data'][0]['attributes']['url']),
            Set::of(Genre::class, ...\array_map(
                static fn(string $genre): Genre => new Genre($genre),
                $resource['data'][0]['attributes']['genreNames'],
            )),
            $this->artistAlbums($resource['data'][0]['relationships']['albums']),
        );
    }

    public function album(Album\Id $id): Album
    {
        /** @var array{data: array{0: array{attributes: array{artwork: array{width: int, height: int, url: string, bgColor: string, textColor1: string, textColor2: string, textColor3: string, textColor4: string}, name: string, isSingle: bool, url: string, isComplete: bool, genreNames: list<string>, isMasteredForItunes: bool, releaseDate: string, recordLabel: string, copyright: string, editorialNotes: array{standard: string, short: string}}, relationships: array{tracks: array{data: list<array{id: int}>}, artists: array{data: list<array{id: int}>}}}}} */
        $resource = $this->get($this->url("albums/$id"));

        return new Album(
            $id,
            new Artwork(
                new Artwork\Width($resource['data'][0]['attributes']['artwork']['width']),
                new Artwork\Height($resource['data'][0]['attributes']['artwork']['height']),
                Url::of($resource['data'][0]['attributes']['artwork']['url']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['bgColor']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor1']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor2']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor3']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor4']),
            ),
            new Album\Name($resource['data'][0]['attributes']['name']),
            $resource['data'][0]['attributes']['isSingle'],
            Url::of($resource['data'][0]['attributes']['url']),
            $resource['data'][0]['attributes']['isComplete'],
            Set::of(Genre::class, ...\array_map(
                static fn(string $genre): Genre => new Genre($genre),
                $resource['data'][0]['attributes']['genreNames'],
            )),
            Set::of(Song\Id::class, ...\array_map(
                static fn(array $song): Song\Id => new Song\Id((int) $song['id']),
                $resource['data'][0]['relationships']['tracks']['data'],
            )),
            $resource['data'][0]['attributes']['isMasteredForItunes'],
            $this->clock->at($resource['data'][0]['attributes']['releaseDate']),
            new Album\RecordLabel($resource['data'][0]['attributes']['recordLabel']),
            new Album\Copyright($resource['data'][0]['attributes']['copyright']),
            new Album\EditorialNotes(
                $resource['data'][0]['attributes']['editorialNotes']['standard'],
                $resource['data'][0]['attributes']['editorialNotes']['short'],
            ),
            Set::of(Artist\Id::class, ...\array_map(
                static fn(array $artist): Artist\Id => new Artist\Id((int) $artist['id']),
                $resource['data'][0]['relationships']['artists']['data'],
            )),
        );
    }

    public function song(Song\Id $id): Song
    {
        /** @var array{data: array{0: array{attributes: array{previews: list<array{url: string}>, artwork: array{width: int, height: int, url: string, bgColor: string, textColor1: string, textColor2: string, textColor3: string, textColor4: string}, url: string, discNumber: int, genreNames: list<string>, durationInMillis: int, releaseDate: string, name: string, isrc: string, trackNumber: int, composerName: string}, relationships: array{artists: array{data: list<array{id: int}>}, albums: array{data: list<array{id: int}>}}}}} */
        $resource = $this->get($this->url("songs/$id"));

        return new Song(
            $id,
            Set::of(Url::class, ...\array_map(
                static fn(array $preview): Url => Url::of($preview['url']),
                $resource['data'][0]['attributes']['previews'],
            )),
            new Artwork(
                new Artwork\Width($resource['data'][0]['attributes']['artwork']['width']),
                new Artwork\Height($resource['data'][0]['attributes']['artwork']['height']),
                Url::of($resource['data'][0]['attributes']['artwork']['url']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['bgColor']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor1']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor2']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor3']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor4']),
            ),
            Url::of($resource['data'][0]['attributes']['url']),
            new Song\DiscNumber($resource['data'][0]['attributes']['discNumber']),
            Set::of(Genre::class, ...\array_map(
                static fn(string $genre): Genre => new Genre($genre),
                $resource['data'][0]['attributes']['genreNames'],
            )),
            new Song\Duration($resource['data'][0]['attributes']['durationInMillis']),
            $this->clock->at($resource['data'][0]['attributes']['releaseDate']),
            new Song\Name($resource['data'][0]['attributes']['name']),
            new Song\ISRC($resource['data'][0]['attributes']['isrc']),
            new Song\TrackNumber($resource['data'][0]['attributes']['trackNumber']),
            new Song\Composer($resource['data'][0]['attributes']['composerName']),
            Set::of(Artist\Id::class, ...\array_map(
                static fn(array $artist): Artist\Id => new Artist\Id((int) $artist['id']),
                $resource['data'][0]['relationships']['artists']['data'],
            )),
            Set::of(Album\Id::class, ...\array_map(
                static fn(array $album): Album\Id => new Album\Id((int) $album['id']),
                $resource['data'][0]['relationships']['albums']['data'],
            )),
        );
    }

    /**
     * @param array{data: list<array{id: int}>, next?: string} $resources
     *
     * @return Set<Album\Id>
     */
    private function artistAlbums(array $resources): Set
    {
        /** @var Set<Album\Id> */
        $albums = Set::of(Album\Id::class);

        foreach ($resources['data'] as $album) {
            $albums = ($albums)(new Album\Id((int) $album['id']));
        }

        if (\array_key_exists('next', $resources)) {
            /** @var array{data: list<array{id: int}>, next?: string} */
            $resources = $this->get(Url::of($resources['next']));

            $albums = $albums->merge($this->artistAlbums($resources));
        }

        return $albums;
    }

    /**
     * @return Set<Genre>
     */
    public function genres(): Set
    {
        $url = $this->url('genres');
        /** @var Set<Genre> */
        $genres = Set::of(Genre::class);

        do {
            /** @var array{data: list<array{attributes: array{name: string}}>, next?: string} */
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $genre) {
                $genres = ($genres)(new Genre(
                    $genre['attributes']['name'],
                ));
            }

            if (\array_key_exists('next', $resource)) {
                $url = Url::of($resource['next']);
            }
        } while ($url instanceof Url);

        return $genres;
    }

    public function search(string $term): Search
    {
        $url = $this->url("search?term=$term&types=artists,albums,songs&limit=25");
        /** @var array{results: array{artists: array{data: list<array{id: int}>, next?: string}, albums: array{data: list<array{id: int}>, next?: string}, songs: array{data: list<array{id: int}>, next?: string}}} */
        $resource = $this->get($url);

        /** @var Sequence<Artist\Id> */
        $artists = Sequence::lazy(
            Artist\Id::class,
            function() use ($resource): \Generator {
                do {
                    foreach ($resource['results']['artists']['data'] as $artist) {
                        yield new Artist\Id((int) $artist['id']);
                    }

                    if (!\array_key_exists('next', $resource['results']['artists'])) {
                        return;
                    }

                    /** @var array{results: array{artists: array{data: list<array{id: int}>, next?: string}}} */
                    $resource = $this->get(Url::of($resource['results']['artists']['next']));
                } while (true);
            },
        );

        /** @var Sequence<Album\Id> */
        $albums = Sequence::lazy(
            Album\Id::class,
            function() use ($resource): \Generator {
                do {
                    foreach ($resource['results']['albums']['data'] as $album) {
                        yield new Album\Id((int) $album['id']);
                    }

                    if (!\array_key_exists('next', $resource['results']['albums'])) {
                        return;
                    }

                    /** @var array{results: array{albums: array{data: list<array{id: int}>, next?: string}}} */
                    $resource = $this->get(Url::of($resource['results']['albums']['next']));
                } while (true);
            },
        );

        /** @var Sequence<Song\Id> */
        $songs = Sequence::lazy(
            Song\Id::class,
            function() use ($resource): \Generator {
                do {
                    foreach ($resource['results']['songs']['data'] as $song) {
                        yield new Song\Id((int) $song['id']);
                    }

                    if (!\array_key_exists('next', $resource['results']['songs'])) {
                        return;
                    }

                    /** @var array{results: array{songs: array{data: list<array{id: int}>, next?: string}}} */
                    $resource = $this->get(Url::of($resource['results']['songs']['next']));
                } while (true);
            },
        );

        return new Search($term, $artists, $albums, $songs);
    }

    private function get(Url $url): array
    {
        $response = ($this->fulfill)(new Request(
            $url,
            Method::get(),
            new ProtocolVersion(2, 0),
            Headers::of($this->authorization),
        ));

        /** @var array */
        return Json::decode($response->body()->toString());
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/catalog/{$this->storefront}/$path");
    }
}
