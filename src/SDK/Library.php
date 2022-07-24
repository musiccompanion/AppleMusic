<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Library\Artist,
    Library\Album,
    Library\Song,
    Storefront,
};
use Innmind\Http\{
    Message\Request\Request,
    Message\Method,
    ProtocolVersion,
    Headers,
    Header,
};
use Innmind\Url\Url;
use Innmind\Json\Json;
use Innmind\Immutable\{
    Set,
    Sequence,
    Maybe,
};

final class Library
{
    private HttpTransport $fulfill;
    private Header $authorization;
    private Header $userToken;

    public function __construct(
        HttpTransport $fulfill,
        Header $authorization,
        Header $userToken,
    ) {
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
        $this->userToken = $userToken;
    }

    public function storefront(): Storefront
    {
        /**
         * @var array{
         *     data: array{
         *         0: array{
         *             id: string,
         *             attributes: array{
         *                 name: string,
         *                 defaultLanguageTag: string,
         *                 supportedLanguageTags: list<string>
         *             }
         *         }
         *     }
         * }
         */
        $resource = $this->get(Url::of('/v1/me/storefront'));

        return new Storefront(
            new Storefront\Id($resource['data'][0]['id']),
            new Storefront\Name($resource['data'][0]['attributes']['name']),
            Storefront\Language::of($resource['data'][0]['attributes']['defaultLanguageTag']),
            Set::of(...$resource['data'][0]['attributes']['supportedLanguageTags'])->map(Storefront\Language::of(...)),
        );
    }

    /**
     * @return Sequence<Artist>
     */
    public function artists(): Sequence
    {
        $url = $this->url('artists?include=catalog');

        /** @var Sequence<Artist> */
        return Sequence::lazy(
            function() use ($url): \Generator {
                do {
                    /**
                     * @var array{
                     *     data: list<array{
                     *         id: string,
                     *         attributes: array{
                     *             name: string
                     *         },
                     *         relationships: array{
                     *             catalog: array{
                     *                 data: list<array{
                     *                     id: string
                     *                 }>
                     *             }
                     *         }
                     *     }>,
                     *     next?: string
                     * }
                     */
                    $resource = $this->get($url);
                    $url = null;

                    foreach ($resource['data'] as $artist) {
                        yield new Artist(
                            Artist\Id::of($artist['id']),
                            new Artist\Name($artist['attributes']['name']),
                            Set::of(...$artist['relationships']['catalog']['data'])
                                ->map(static fn($artist) => (int) $artist['id'])
                                ->map(Catalog\Artist\Id::of(...))
                                ->find(static fn() => true), // the documentation say there's at most one artist
                        );
                    }

                    if (\array_key_exists('next', $resource)) {
                        $url = Url::of($resource['next'].'&include=catalog');
                    }
                } while ($url instanceof Url);
            },
        );
    }

    /**
     * @return Set<Album>
     */
    public function albums(Artist\Id $artist): Set
    {
        $url = $this->url("artists/{$artist->toString()}/albums?include=artists");
        /** @var Set<Album> */
        $albums = Set::of();

        do {
            /**
             * @var array{
             *     data: list<array{
             *         id: string,
             *         attributes: array{
             *             name: string,
             *             artwork?: array{
             *                 width: int,
             *                 height: int,
             *                 url: string
             *             }
             *         },
             *         relationships: array{
             *             artists: array{
             *                 data: list<array{id: string}>
             *             }
             *         }
             *     }>,
             *     next?: string
             * }
             */
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $album) {
                $albums = ($albums)(new Album(
                    Album\Id::of($album['id']),
                    new Album\Name($album['attributes']['name']),
                    Maybe::of($album['attributes']['artwork'] ?? null)->map(
                        static fn($artwork) => new Album\Artwork(
                            Maybe::of($artwork['width'])->map(Album\Artwork\Width::of(...)),
                            Maybe::of($artwork['height'])->map(Album\Artwork\Height::of(...)),
                            Url::of($artwork['url']),
                        ),
                    ),
                    Set::of(...$album['relationships']['artists']['data'])
                        ->map(static fn($artist) => $artist['id'])
                        ->map(Artist\Id::of(...)),
                ));
            }

            if (\array_key_exists('next', $resource)) {
                $url = Url::of($resource['next'].'&include=artists');
            }
        } while ($url instanceof Url);

        return $albums;
    }

    /**
     * @return Set<Song>
     */
    public function songs(Album\Id $album): Set
    {
        $url = $this->url("albums/{$album->toString()}/tracks?include=albums,artists");
        /** @var Set<Song> */
        $songs = Set::of();

        do {
            /**
             * @var array{
             *     data: list<array{
             *         id: string,
             *         attributes: array{
             *             name: string,
             *             durationInMillis: int,
             *             trackNumber?: int,
             *             genreNames: list<string>
             *         },
             *         relationships: array{
             *             albums: array{
             *                 data: list<array{id: string}>
             *             },
             *             artists: array{
             *                 data: list<array{id: string}>
             *             }
             *         }
             *     }>,
             *     next?: string
             * }
             */
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $song) {
                $songs = ($songs)(new Song(
                    new Song\Id($song['id']),
                    new Song\Name($song['attributes']['name']),
                    Maybe::of($song['attributes']['durationInMillis'] ?? null)->map(Song\Duration::of(...)),
                    Maybe::of($song['attributes']['trackNumber'] ?? null)->map(Song\TrackNumber::of(...)),
                    Set::of(...$song['attributes']['genreNames'])->map(Song\Genre::of(...)),
                    Set::of(...$song['relationships']['albums']['data'])
                        ->map(static fn($album) => $album['id'])
                        ->map(Album\Id::of(...)),
                    Set::of(...$song['relationships']['artists']['data'])
                        ->map(static fn($artist) => $artist['id'])
                        ->map(Artist\Id::of(...)),
                ));
            }

            if (\array_key_exists('next', $resource)) {
                $url = Url::of($resource['next'].'&include=albums,artists');
            }
        } while ($url instanceof Url);

        return $songs;
    }

    private function get(Url $url): array
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $response = ($this->fulfill)(new Request(
            $url,
            Method::get,
            ProtocolVersion::v20,
            Headers::of(
                $this->authorization,
                $this->userToken,
            ),
        ))->match(
            static fn($response) => $response,
            static fn() => throw new \RuntimeException,
        );

        /** @var array */
        return Json::decode($response->body()->toString());
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/me/library/$path");
    }
}
