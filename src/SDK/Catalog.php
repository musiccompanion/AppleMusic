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
    Maybe,
};

final class Catalog
{
    private Clock $clock;
    private HttpTransport $fulfill;
    private Header $authorization;
    private Storefront\Id $storefront;

    public function __construct(
        Clock $clock,
        HttpTransport $fulfill,
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
        /**
         * @var array{
         *     data: array{
         *         0: array{
         *             attributes: array{
         *                 name: string,
         *                 url: string,
         *                 genreNames: list<string>
         *             },
         *             relationships: array{
         *                 albums: array{
         *                     data: list<array{id: int}>,
         *                     next?: string
         *                 }
         *             }
         *         }
         *     }
         * }
         */
        $resource = $this->get($this->url("artists/{$id->toString()}"));

        return new Artist(
            $id,
            new Artist\Name($resource['data'][0]['attributes']['name']),
            Url::of($resource['data'][0]['attributes']['url']),
            Set::of(...$resource['data'][0]['attributes']['genreNames'])->map(Genre::of(...)),
            $this->artistAlbums($resource['data'][0]['relationships']['albums']),
        );
    }

    public function album(Album\Id $id): Album
    {
        /**
         * @var array{
         *     data: array{
         *         0: array{
         *             attributes: array{
         *                 artwork?: array{
         *                     width: int,
         *                     height: int,
         *                     url: string,
         *                     bgColor?: string,
         *                     textColor1?: string,
         *                     textColor2?: string,
         *                     textColor3?: string,
         *                     textColor4?: string
         *                 },
         *                 name: string,
         *                 isSingle: bool,
         *                 url: string,
         *                 isComplete: bool,
         *                 genreNames: list<string>,
         *                 isMasteredForItunes: bool,
         *                 releaseDate: string,
         *                 recordLabel: string,
         *                 copyright?: string,
         *                 editorialNotes?: array{
         *                     standard: string,
         *                     short: string
         *                 }
         *             },
         *             relationships: array{
         *                 tracks: array{
         *                     data: list<array{id: int}>
         *                 },
         *                 artists: array{
         *                     data: list<array{id: int}>
         *                 }
         *             }
         *         }
         *     }
         * }
         */
        $resource = $this->get($this->url("albums/{$id->toString()}"));
        $attributes = $resource['data'][0]['attributes'];
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
            Maybe::of($attributes['artwork'] ?? null)->map(
                static fn($artwork) => new Artwork(
                    new Artwork\Width($artwork['width']),
                    new Artwork\Height($artwork['height']),
                    Url::of($artwork['url']),
                    Maybe::of($artwork['bgColor'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($artwork['textColor1'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($artwork['textColor2'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($artwork['textColor3'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($artwork['textColor4'] ?? null)->map(RGBA::of(...)),
                ),
            ),
            new Album\Name($attributes['name']),
            $attributes['isSingle'],
            Url::of($attributes['url']),
            $attributes['isComplete'],
            Set::of(...$attributes['genreNames'])->map(Genre::of(...)),
            Set::of(...$resource['data'][0]['relationships']['tracks']['data'])
                ->map(static fn($song) => (int) $song['id'])
                ->map(Song\Id::of(...)),
            $attributes['isMasteredForItunes'],
            $this->clock->at($releaseDate)->match(
                static fn($date) => $date,
                static fn() => throw new \RuntimeException,
            ),
            new Album\RecordLabel($attributes['recordLabel'] ?? ''),
            new Album\Copyright($attributes['copyright'] ?? ''),
            new Album\EditorialNotes(
                $attributes['editorialNotes']['standard'] ?? '',
                $attributes['editorialNotes']['short'] ?? '',
            ),
            Set::of(...$resource['data'][0]['relationships']['artists']['data'])
                ->map(static fn($artist) => (int) $artist['id'])
                ->map(Artist\Id::of(...)),
        );
    }

    public function song(Song\Id $id): Song
    {
        /**
         * @var array{
         *     data: array{
         *         0: array{
         *             attributes: array{
         *                 previews: list<array{url: string}>,
         *                 artwork: array{
         *                     width: int,
         *                     height: int,
         *                     url: string,
         *                     bgColor?: string,
         *                     textColor1?: string,
         *                     textColor2?: string,
         *                     textColor3?: string,
         *                     textColor4?: string
         *                 },
         *                 url: string,
         *                 discNumber: int,
         *                 genreNames: list<string>,
         *                 durationInMillis?: int,
         *                 releaseDate: string,
         *                 name: string,
         *                 isrc: string,
         *                 trackNumber: int,
         *                 composerName?: string
         *             },
         *             relationships: array{
         *                 artists: array{
         *                     data: list<array{id: int}>
         *                 },
         *                 albums: array{
         *                     data: list<array{id: int}>
         *                 }
         *             }
         *         }
         *     }
         * }
         */
        $resource = $this->get($this->url("songs/{$id->toString()}"));
        $attributes = $resource['data'][0]['attributes'];

        /** @psalm-suppress RedundantCastGivenDocblockType */
        return new Song(
            $id,
            Set::of(...$attributes['previews'])->map(
                static fn($preview) => Url::of($preview['url']),
            ),
            new Artwork(
                new Artwork\Width($attributes['artwork']['width']),
                new Artwork\Height($attributes['artwork']['height']),
                Url::of($attributes['artwork']['url']),
                Maybe::of($attributes['artwork']['bgColor'] ?? null)->map(RGBA::of(...)),
                Maybe::of($attributes['artwork']['textColor1'] ?? null)->map(RGBA::of(...)),
                Maybe::of($attributes['artwork']['textColor2'] ?? null)->map(RGBA::of(...)),
                Maybe::of($attributes['artwork']['textColor3'] ?? null)->map(RGBA::of(...)),
                Maybe::of($attributes['artwork']['textColor4'] ?? null)->map(RGBA::of(...)),
            ),
            Url::of($attributes['url']),
            new Song\DiscNumber($attributes['discNumber']),
            Set::of(...$attributes['genreNames'])->map(Genre::of(...)),
            Maybe::of($attributes['durationInMillis'] ?? null)->map(Song\Duration::of(...)),
            $this->clock->at($attributes['releaseDate'])->match(
                static fn($date) => $date,
                static fn() => throw new \RuntimeException,
            ),
            new Song\Name($attributes['name']),
            new Song\ISRC($attributes['isrc']),
            new Song\TrackNumber($attributes['trackNumber']),
            new Song\Composer($attributes['composerName'] ?? ''),
            Set::of(...$resource['data'][0]['relationships']['artists']['data'])
                ->map(static fn($artist) => (int) $artist['id'])
                ->map(Artist\Id::of(...)),
            Set::of(...$resource['data'][0]['relationships']['albums']['data'])
                ->map(static fn($album) => (int) $album['id'])
                ->map(Album\Id::of(...)),
        );
    }

    /**
     * @return Set<Genre>
     */
    public function genres(): Set
    {
        $url = $this->url('genres');
        /** @var Set<Genre> */
        $genres = Set::of();

        do {
            /**
             * @var array{
             *     data: list<array{
             *         attributes: array{
             *             name: string
             *         }
             *     }>,
             *     next?: string
             * }
             */
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $genre) {
                $genres = ($genres)(Genre::of(
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
        /**
         * @var array{
         *     results: array{
         *         artists?: array{
         *             data: list<array{id: int}>,
         *             next?: string
         *         },
         *         albums?: array{
         *             data: list<array{id: int}>,
         *             next?: string
         *         },
         *         songs?: array{
         *             data: list<array{id: int}>,
         *             next?: string
         *         }
         *     }
         * }
         */
        $resource = $this->get($url);

        $artists = Sequence::lazy(
            function() use ($resource): \Generator {
                do {
                    $artists = $resource['results']['artists'] ?? [];

                    foreach ($artists['data'] ?? [] as $artist) {
                        /** @psalm-suppress RedundantCastGivenDocblockType */
                        yield Artist\Id::of((int) $artist['id']);
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
            function() use ($resource): \Generator {
                do {
                    $albums = $resource['results']['albums'] ?? [];

                    foreach ($albums['data'] ?? [] as $album) {
                        /** @psalm-suppress RedundantCastGivenDocblockType */
                        yield Album\Id::of((int) $album['id']);
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
            function() use ($resource): \Generator {
                do {
                    $songs = $resource['results']['songs'] ?? [];

                    foreach ($songs['data'] ?? [] as $song) {
                        /** @psalm-suppress RedundantCastGivenDocblockType */
                        yield Song\Id::of((int) $song['id']);
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
        $albums = Set::of();

        foreach ($resources['data'] as $album) {
            /** @psalm-suppress RedundantCastGivenDocblockType */
            $albums = ($albums)(Album\Id::of((int) $album['id']));
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
            Method::get,
            ProtocolVersion::v20,
            Headers::of($this->authorization),
        ))->match(
            static fn($response) => $response,
            static fn() => throw new \RuntimeException,
        );

        /** @var array */
        return Json::decode($response->body()->toString());
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/catalog/{$this->storefront->toString()}/$path");
    }
}
