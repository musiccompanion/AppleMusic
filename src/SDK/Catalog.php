<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
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
    Header,
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
    Str,
};

final class Catalog
{
    private Clock $clock;
    private Transport $fulfill;
    private Header $authorization;
    private Storefront\Id $storefront;

    public function __construct(
        Clock $clock,
        Transport $fulfill,
        Header $authorization,
        Storefront\Id $storefront,
    ) {
        $this->clock = $clock;
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
        $this->storefront = $storefront;
    }

    public function artist(Artist\Id $id): Artist
    {
        /** @var array{data: array{0: array{attributes: array{name: string, url: string, genreNames: list<string>}, relationships: array{albums: array{data: list<array{id: int}>, next?: string}}}}} */
        $resource = $this->get($this->url("artists/{$id->toString()}"));

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
        /** @var array{data: array{0: array{attributes: array{artwork?: array{width: int, height: int, url: string, bgColor?: string, textColor1?: string, textColor2?: string, textColor3?: string, textColor4?: string}, name: string, isSingle: bool, url: string, isComplete: bool, genreNames: list<string>, isMasteredForItunes: bool, releaseDate: string, recordLabel: string, copyright?: string, editorialNotes?: array{standard: string, short: string}}, relationships: array{tracks: array{data: list<array{id: int}>}, artists: array{data: list<array{id: int}>}}}}} */
        $resource = $this->get($this->url("albums/{$id->toString()}"));
        $attributes = $resource['data'][0]['attributes'];
        $bgColor = $attributes['artwork']['bgColor'] ?? null;
        $textColor1 = $attributes['artwork']['textColor1'] ?? null;
        $textColor2 = $attributes['artwork']['textColor2'] ?? null;
        $textColor3 = $attributes['artwork']['textColor3'] ?? null;
        $textColor4 = $attributes['artwork']['textColor4'] ?? null;
        $releaseDate = $attributes['releaseDate'];

        if (Str::of($releaseDate)->matches('~^\d{4}$~')) {
            // like in the case of this EP https://music.apple.com/fr/album/rip-it-up-ep/213587444
            // only the year is provided, and Apple Music interprets this as
            // january 1st
            $releaseDate .= '-01-01';
        }

        if (Str::of($releaseDate)->matches('~^\d{4}-\d{2}-\d{2}$~')) {
            $releaseDate .= ' 00:00:00';
        }

        /** @psalm-suppress RedundantCastGivenDocblockType */
        return new Album(
            $id,
            \array_key_exists('artwork', $attributes) ? new Artwork(
                new Artwork\Width($attributes['artwork']['width']),
                new Artwork\Height($attributes['artwork']['height']),
                Url::of($attributes['artwork']['url']),
                \is_string($bgColor) ? RGBA::of($bgColor) : null,
                \is_string($textColor1) ? RGBA::of($textColor1) : null,
                \is_string($textColor2) ? RGBA::of($textColor2) : null,
                \is_string($textColor3) ? RGBA::of($textColor3) : null,
                \is_string($textColor4) ? RGBA::of($textColor4) : null,
            ) : null,
            new Album\Name($attributes['name']),
            $attributes['isSingle'],
            Url::of($attributes['url']),
            $attributes['isComplete'],
            Set::of(Genre::class, ...\array_map(
                static fn(string $genre): Genre => new Genre($genre),
                $attributes['genreNames'],
            )),
            Set::of(Song\Id::class, ...\array_map(
                static fn(array $song): Song\Id => new Song\Id((int) $song['id']),
                $resource['data'][0]['relationships']['tracks']['data'],
            )),
            $attributes['isMasteredForItunes'],
            $this->clock->at($releaseDate),
            new Album\RecordLabel($attributes['recordLabel'] ?? ''),
            new Album\Copyright($attributes['copyright'] ?? ''),
            new Album\EditorialNotes(
                $attributes['editorialNotes']['standard'] ?? '',
                $attributes['editorialNotes']['short'] ?? '',
            ),
            Set::of(Artist\Id::class, ...\array_map(
                static fn(array $artist): Artist\Id => new Artist\Id((int) $artist['id']),
                $resource['data'][0]['relationships']['artists']['data'],
            )),
        );
    }

    public function song(Song\Id $id): Song
    {
        /** @var array{data: array{0: array{attributes: array{previews: list<array{url: string}>, artwork: array{width: int, height: int, url: string, bgColor?: string, textColor1?: string, textColor2?: string, textColor3?: string, textColor4?: string}, url: string, discNumber: int, genreNames: list<string>, durationInMillis?: int, releaseDate: string, name: string, isrc: string, trackNumber: int, composerName?: string}, relationships: array{artists: array{data: list<array{id: int}>}, albums: array{data: list<array{id: int}>}}}}} */
        $resource = $this->get($this->url("songs/{$id->toString()}"));
        $attributes = $resource['data'][0]['attributes'];
        $bgColor = $attributes['artwork']['bgColor'] ?? null;
        $textColor1 = $attributes['artwork']['textColor1'] ?? null;
        $textColor2 = $attributes['artwork']['textColor2'] ?? null;
        $textColor3 = $attributes['artwork']['textColor3'] ?? null;
        $textColor4 = $attributes['artwork']['textColor4'] ?? null;

        /** @psalm-suppress RedundantCastGivenDocblockType */
        return new Song(
            $id,
            Set::of(Url::class, ...\array_map(
                static fn(array $preview): Url => Url::of($preview['url']),
                $attributes['previews'],
            )),
            new Artwork(
                new Artwork\Width($attributes['artwork']['width']),
                new Artwork\Height($attributes['artwork']['height']),
                Url::of($attributes['artwork']['url']),
                \is_string($bgColor) ? RGBA::of($bgColor) : null,
                \is_string($textColor1) ? RGBA::of($textColor1) : null,
                \is_string($textColor2) ? RGBA::of($textColor2) : null,
                \is_string($textColor3) ? RGBA::of($textColor3) : null,
                \is_string($textColor4) ? RGBA::of($textColor4) : null,
            ),
            Url::of($attributes['url']),
            new Song\DiscNumber($attributes['discNumber']),
            Set::of(Genre::class, ...\array_map(
                static fn(string $genre): Genre => new Genre($genre),
                $attributes['genreNames'],
            )),
            Song\Duration::of($attributes['durationInMillis'] ?? null),
            $this->clock->at($attributes['releaseDate']),
            new Song\Name($attributes['name']),
            new Song\ISRC($attributes['isrc']),
            new Song\TrackNumber($attributes['trackNumber']),
            new Song\Composer($attributes['composerName'] ?? ''),
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
        $encodedTerm = \urlencode($term);
        $url = $this->url("search?term=$encodedTerm&types=artists,albums,songs&limit=25");
        /** @var array{results: array{artists?: array{data: list<array{id: int}>, next?: string}, albums?: array{data: list<array{id: int}>, next?: string}, songs?: array{data: list<array{id: int}>, next?: string}}} */
        $resource = $this->get($url);

        $artists = Sequence::lazy(
            Artist\Id::class,
            function() use ($resource): \Generator {
                do {
                    $artists = $resource['results']['artists'] ?? [];

                    foreach ($artists['data'] ?? [] as $artist) {
                        /** @psalm-suppress RedundantCastGivenDocblockType */
                        yield new Artist\Id((int) $artist['id']);
                    }

                    if (!\array_key_exists('next', $artists)) {
                        return;
                    }

                    /** @var array{results: array{artists: array{data: list<array{id: int}>, next?: string}}} */
                    $resource = $this->get(Url::of($artists['next']));
                } while (true);
            },
        );

        $albums = Sequence::lazy(
            Album\Id::class,
            function() use ($resource): \Generator {
                do {
                    $albums = $resource['results']['albums'] ?? [];

                    foreach ($albums['data'] ?? [] as $album) {
                        /** @psalm-suppress RedundantCastGivenDocblockType */
                        yield new Album\Id((int) $album['id']);
                    }

                    if (!\array_key_exists('next', $albums)) {
                        return;
                    }

                    /** @var array{results: array{albums: array{data: list<array{id: int}>, next?: string}}} */
                    $resource = $this->get(Url::of($albums['next']));
                } while (true);
            },
        );

        $songs = Sequence::lazy(
            Song\Id::class,
            function() use ($resource): \Generator {
                do {
                    $songs = $resource['results']['songs'] ?? [];

                    foreach ($songs['data'] ?? [] as $song) {
                        /** @psalm-suppress RedundantCastGivenDocblockType */
                        yield new Song\Id((int) $song['id']);
                    }

                    if (!\array_key_exists('next', $songs)) {
                        return;
                    }

                    /** @var array{results: array{songs: array{data: list<array{id: int}>, next?: string}}} */
                    $resource = $this->get(Url::of($songs['next']));
                } while (true);
            },
        );

        return new Search($term, $artists, $albums, $songs);
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
            /** @psalm-suppress RedundantCastGivenDocblockType */
            $albums = ($albums)(new Album\Id((int) $album['id']));
        }

        if (\array_key_exists('next', $resources)) {
            /** @var array{data: list<array{id: int}>, next?: string} */
            $resources = $this->get(Url::of($resources['next']));

            $albums = $albums->merge($this->artistAlbums($resources));
        }

        return $albums;
    }

    private function get(Url $url): array
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
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
        return Url::of("/v1/catalog/{$this->storefront->toString()}/$path");
    }
}
