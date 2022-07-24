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
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
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

/**
 * @psalm-type Artists = array{
 *     data: list<array{
 *         attributes: array{
 *             name: string,
 *             url: string,
 *             genreNames: list<string>,
 *             artwork?: array{
 *                 url: string,
 *                 height: int,
 *                 width: int,
 *                 bgColor?: string,
 *                 textColor1?: string,
 *                 textColor2?: string,
 *                 textColor3?: string,
 *                 textColor4?: string,
 *             },
 *         },
 *         relationships: array{
 *             albums: array{
 *                 data: list<array{id: string}>,
 *                 next?: string
 *             }
 *         }
 *     }>
 * }
 * @psalm-type Albums = array{
 *     data: list<array{
 *         attributes: array{
 *             artwork: array{
 *                 width: int,
 *                 height: int,
 *                 url: string,
 *                 bgColor?: string,
 *                 textColor1?: string,
 *                 textColor2?: string,
 *                 textColor3?: string,
 *                 textColor4?: string
 *             },
 *             name: string,
 *             isSingle: bool,
 *             url: string,
 *             isComplete: bool,
 *             genreNames: list<string>,
 *             isMasteredForItunes: bool,
 *             releaseDate?: string,
 *             recordLabel?: string,
 *             copyright?: string,
 *             editorialNotes?: array{
 *                 standard?: string,
 *                 short?: string
 *             }
 *         },
 *         relationships: array{
 *             tracks: array{
 *                 data: list<array{id: string}>
 *             },
 *             artists: array{
 *                 data: list<array{id: string}>
 *             }
 *         }
 *     }>
 * }
 * @psalm-type Songs = array{
 *     data: list<array{
 *         attributes: array{
 *             previews: list<array{url: string}>,
 *             artwork: array{
 *                 width: int,
 *                 height: int,
 *                 url: string,
 *                 bgColor?: string,
 *                 textColor1?: string,
 *                 textColor2?: string,
 *                 textColor3?: string,
 *                 textColor4?: string
 *             },
 *             url: string,
 *             discNumber?: int,
 *             genreNames: list<string>,
 *             durationInMillis: int,
 *             releaseDate?: string,
 *             name: string,
 *             isrc?: string,
 *             trackNumber?: int,
 *             composerName?: string
 *         },
 *         relationships: array{
 *             artists: array{
 *                 data: list<array{id: string}>
 *             },
 *             albums: array{
 *                 data: list<array{id: string}>
 *             }
 *         }
 *     }>
 * }
 * @psalm-type Search = array{
 *     results: array{
 *         artists?: array{
 *             data: list<array{id: string}>,
 *             next?: string
 *         },
 *         albums?: array{
 *             data: list<array{id: string}>,
 *             next?: string
 *         },
 *         songs?: array{
 *             data: list<array{id: string}>,
 *             next?: string
 *         }
 *     }
 * }
 */
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

    /**
     * @return Maybe<Artist>
     */
    public function artist(Artist\Id $id): Maybe
    {
        return $this
            ->get($this->url("artists/{$id->toString()}?include=albums"))
            ->map($this->decodeArtists(...))
            ->map(static fn($artists) => Sequence::of(...$artists['data']))
            ->flatMap(static fn($artists) => $artists->first())
            ->map(fn($artist) => new Artist(
                $id,
                new Artist\Name($artist['attributes']['name']),
                Url::of($artist['attributes']['url']),
                Set::of(...$artist['attributes']['genreNames'])->map(Genre::of(...)),
                $this->artistAlbums($artist['relationships']['albums']),
                Maybe::of($artist['attributes']['artwork'] ?? null)->map(
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
            ));
    }

    /**
     * @return Maybe<Album>
     */
    public function album(Album\Id $id): Maybe
    {
        return $this
            ->get($this->url("albums/{$id->toString()}?include=artists,tracks"))
            ->map($this->decodeAlbums(...))
            ->map(static fn($albums) => Sequence::of(...$albums['data']))
            ->flatMap(static fn($albums) => $albums->first())
            ->map(fn($album) => new Album(
                $id,
                new Artwork(
                    new Artwork\Width($album['attributes']['artwork']['width']),
                    new Artwork\Height($album['attributes']['artwork']['height']),
                    Url::of($album['attributes']['artwork']['url']),
                    Maybe::of($album['attributes']['artwork']['bgColor'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($album['attributes']['artwork']['textColor1'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($album['attributes']['artwork']['textColor2'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($album['attributes']['artwork']['textColor3'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($album['attributes']['artwork']['textColor4'] ?? null)->map(RGBA::of(...)),
                ),
                new Album\Name($album['attributes']['name']),
                $album['attributes']['isSingle'],
                Url::of($album['attributes']['url']),
                $album['attributes']['isComplete'],
                Set::of(...$album['attributes']['genreNames'])->map(Genre::of(...)),
                Set::of(...$album['relationships']['tracks']['data'])
                    ->map(static fn($song) => (int) $song['id'])
                    ->map(Song\Id::of(...)),
                $album['attributes']['isMasteredForItunes'],
                $this->releaseDate($album['attributes']['releaseDate'] ?? null),
                new Album\RecordLabel($album['attributes']['recordLabel'] ?? ''),
                new Album\Copyright($album['attributes']['copyright'] ?? ''),
                new Album\EditorialNotes(
                    $album['attributes']['editorialNotes']['standard'] ?? '',
                    $album['attributes']['editorialNotes']['short'] ?? '',
                ),
                Set::of(...$album['relationships']['artists']['data'])
                    ->map(static fn($artist) => (int) $artist['id'])
                    ->map(Artist\Id::of(...)),
            ));
    }

    /**
     * @return Maybe<Song>
     */
    public function song(Song\Id $id): Maybe
    {
        return $this
            ->get($this->url("songs/{$id->toString()}?include=artists,albums"))
            ->map($this->decodeSongs(...))
            ->map(static fn($songs) => Sequence::of(...$songs['data']))
            ->flatMap(static fn($songs) => $songs->first())
            ->map(fn($song) => new Song(
                $id,
                Set::of(...$song['attributes']['previews'])->map(
                    static fn($preview) => Url::of($preview['url']),
                ),
                new Artwork(
                    new Artwork\Width($song['attributes']['artwork']['width']),
                    new Artwork\Height($song['attributes']['artwork']['height']),
                    Url::of($song['attributes']['artwork']['url']),
                    Maybe::of($song['attributes']['artwork']['bgColor'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($song['attributes']['artwork']['textColor1'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($song['attributes']['artwork']['textColor2'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($song['attributes']['artwork']['textColor3'] ?? null)->map(RGBA::of(...)),
                    Maybe::of($song['attributes']['artwork']['textColor4'] ?? null)->map(RGBA::of(...)),
                ),
                Url::of($song['attributes']['url']),
                Maybe::of($song['attributes']['discNumber'] ?? null)->map(Song\DiscNumber::of(...)),
                Set::of(...$song['attributes']['genreNames'])->map(Genre::of(...)),
                Maybe::of($song['attributes']['durationInMillis'] ?? null)->map(Song\Duration::of(...)),
                $this->releaseDate($song['attributes']['releaseDate'] ?? null),
                new Song\Name($song['attributes']['name']),
                Maybe::of($song['attributes']['isrc'] ?? null)->map(Song\ISRC::of(...)),
                Maybe::of($song['attributes']['trackNumber'] ?? null)->map(Song\TrackNumber::of(...)),
                new Song\Composer($song['attributes']['composerName'] ?? ''),
                Set::of(...$song['relationships']['artists']['data'])
                    ->map(static fn($artist) => (int) $artist['id'])
                    ->map(Artist\Id::of(...)),
                Set::of(...$song['relationships']['albums']['data'])
                    ->map(static fn($album) => (int) $album['id'])
                    ->map(Album\Id::of(...)),
            ));
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
            $resource = $this
                ->get($url)
                ->map(Json::decode(...))
                ->match(
                    static fn($genres): mixed => $genres,
                    static fn() => ['data' => []],
                );
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
         * @var Search
         */
        $resource = $this
            ->get($url)
            ->map(Json::decode(...))
            ->match(
                static fn($result): mixed => $result,
                static fn() => ['results' => []],
            );

        $artists = Sequence::lazy(
            function() use ($resource): \Generator {
                do {
                    $artists = $resource['results']['artists'] ?? [];

                    foreach ($artists['data'] ?? [] as $artist) {
                        yield Artist\Id::of((int) $artist['id']);
                    }

                    if (!\array_key_exists('next', $artists)) {
                        return;
                    }

                    /** @var Search */
                    $resource = $this
                        ->get(Url::of($artists['next']))
                        ->map(Json::decode(...))
                        ->match(
                            static fn($result): mixed => $result,
                            static fn() => ['results' => []],
                        );
                } while (true);
            },
        );

        $albums = Sequence::lazy(
            function() use ($resource): \Generator {
                do {
                    $albums = $resource['results']['albums'] ?? [];

                    foreach ($albums['data'] ?? [] as $album) {
                        yield Album\Id::of((int) $album['id']);
                    }

                    if (!\array_key_exists('next', $albums)) {
                        return;
                    }

                    /** @var Search */
                    $resource = $this
                        ->get(Url::of($albums['next']))
                        ->map(Json::decode(...))
                        ->match(
                            static fn($result): mixed => $result,
                            static fn() => ['results' => []],
                        );
                } while (true);
            },
        );

        $songs = Sequence::lazy(
            function() use ($resource): \Generator {
                do {
                    $songs = $resource['results']['songs'] ?? [];

                    foreach ($songs['data'] ?? [] as $song) {
                        yield Song\Id::of((int) $song['id']);
                    }

                    if (!\array_key_exists('next', $songs)) {
                        return;
                    }

                    /** @var Search */
                    $resource = $this
                        ->get(Url::of($songs['next']))
                        ->map(Json::decode(...))
                        ->match(
                            static fn($result): mixed => $result,
                            static fn() => ['results' => []],
                        );
                } while (true);
            },
        );

        return new Search($term, $artists, $albums, $songs);
    }

    /**
     * @param array{data: list<array{id: string}>, next?: string} $resources
     *
     * @return Set<Album\Id>
     */
    private function artistAlbums(array $resources): Set
    {
        /** @var Set<Album\Id> */
        $albums = Set::of();

        foreach ($resources['data'] as $album) {
            $albums = ($albums)(Album\Id::of((int) $album['id']));
        }

        if (\array_key_exists('next', $resources)) {
            /** @var array{data: list<array{id: string}>, next?: string} */
            $resources = $this
                ->get(Url::of($resources['next']))
                ->map(Json::decode(...))
                ->match(
                    static fn($albums): mixed => $albums,
                    static fn() => ['data' => []],
                );

            $albums = $albums->merge($this->artistAlbums($resources));
        }

        return $albums;
    }

    /**
     * @return Maybe<string>
     */
    private function get(Url $url): Maybe
    {
        return ($this->fulfill)(new Request(
            $url,
            Method::get,
            ProtocolVersion::v20,
            Headers::of($this->authorization),
        ))->map(static fn($response) => $response->body()->toString());
    }

    /**
     * @return Artists
     */
    private function decodeArtists(string $content): array
    {
        /** @var Artists */
        return Json::decode($content);
    }

    /**
     * @return Albums
     */
    private function decodeAlbums(string $content): array
    {
        /** @var Albums */
        return Json::decode($content);
    }

    /**
     * @return Songs
     */
    private function decodeSongs(string $content): array
    {
        /** @var Songs */
        return Json::decode($content);
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/catalog/{$this->storefront->toString()}/$path");
    }

    /**
     * @return Maybe<PointInTime>
     */
    private function releaseDate(?string $releaseDate): Maybe
    {
        return Maybe::of($releaseDate)
            ->map(Str::of(...))
            // like in the case of this EP https://music.apple.com/fr/album/rip-it-up-ep/213587444
            // only the year is provided, and Apple Music interprets this as
            // january 1st
            ->map(static fn($date) => match ($date->matches('~^\d{4}$~')) {
                true => $date->append('-01-01'),
                false => $date,
            })
            ->flatMap(fn($date) => $this->clock->at(
                $date->toString(),
                new ReleaseDate,
            ));
    }
}
