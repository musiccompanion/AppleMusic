<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\{
    Library\Artist,
    Library\Album,
    Library\Song,
};
use Innmind\Http\{
    Request,
    Response,
    Method,
    ProtocolVersion,
    Headers,
    Header,
};
use Innmind\Url\Url;
use Innmind\Validation\{
    Constraint,
    Is,
};
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
    private Storefront $storefront;

    public function __construct(
        HttpTransport $fulfill,
        Header $authorization,
        Header $userToken,
        Storefront $storefront,
    ) {
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
        $this->userToken = $userToken;
        $this->storefront = $storefront;
    }

    /**
     * @return Maybe<self>
     */
    public static function of(
        HttpTransport $fulfill,
        Header $authorization,
        Header $userToken,
    ): Maybe {
        return $fulfill(Request::of(
            Url::of('/v1/me/storefront'),
            Method::get,
            ProtocolVersion::v20,
            Headers::of(
                $authorization,
                $userToken,
            ),
        ))
            ->flatMap(self::decoreStorefronts(...))
            ->map(static fn($storefront) => new self(
                $fulfill,
                $authorization,
                $userToken,
                $storefront,
            ));
    }

    public function storefront(): Storefront
    {
        return $this->storefront;
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
                /**
                 * @psalm-suppress MixedArrayAccess
                 * @psalm-suppress MixedArgument
                 * @var Constraint<mixed, array{data: Sequence<Artist>, next?: Url}>
                 */
                $validate = Is::shape(
                    'data',
                    Is::list(
                        Is::shape(
                            'id',
                            Is::string()->map(Artist\Id::of(...)),
                        )
                            ->with('attributes', Is::shape(
                                'name',
                                Is::string()->map(Artist\Name::of(...)),
                            ))
                            ->with('relationships', Is::shape(
                                'catalog',
                                Is::shape(
                                    'data',
                                    Is::list(
                                        Is::shape(
                                            'id',
                                            Is::string()
                                                ->map(static fn($value) => (int) $value)
                                                ->map(Catalog\Artist\Id::of(...)),
                                        ),
                                    )->map(
                                        // the documentation say there's at most one artist
                                        static fn($values) => Set::of(...$values)
                                            ->map(static fn($value): mixed => $value['id'])
                                            ->find(static fn() => true),
                                    ),
                                ),
                            ))
                            ->map(static fn($artist) => Artist::of(
                                $artist['id'],
                                $artist['attributes']['name'],
                                $artist['relationships']['catalog']['data'],
                            )),
                    )->map(static fn($artists) => Sequence::of(...$artists)),
                )
                    ->optional(
                        'next',
                        Is::string()->map(
                            static fn($next) => Url::of($next.'&include=catalog'),
                        ),
                    );

                do {
                    [$artists, $url] = $this
                        ->get($url)
                        ->flatMap(static fn($response) => $validate($response)->maybe())
                        ->match(
                            static fn($response) => [$response['data'], $response['next'] ?? null],
                            static fn() => [Sequence::of(), null],
                        );

                    yield $artists;
                } while ($url instanceof Url);
            },
        )->flatMap(static fn($artists) => $artists);
    }

    /**
     * @return Sequence<Album>
     */
    public function albums(Artist\Id $artist): Sequence
    {
        return Sequence::lazy(function() use ($artist) {
            $url = $this->url("artists/{$artist->toString()}/albums?include=artists");
            /**
             * @psalm-suppress MixedArrayAccess
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedArgumentTypeCoercion
             * @psalm-suppress InvalidArgument
             * @var Constraint<mixed, array{data: Sequence<Album>, next?: Url}>
             */
            $validate = Is::shape(
                'data',
                Is::list(
                    Is::shape(
                        'id',
                        Is::string()->map(Album\Id::of(...)),
                    )
                        ->with(
                            'attributes',
                            Is::shape(
                                'name',
                                Is::string()->map(Album\Name::of(...)),
                            )
                                ->optional(
                                    'artwork',
                                    Is::shape(
                                        'url',
                                        Is::string()->map(Url::of(...)),
                                    )
                                        ->optional(
                                            'width',
                                            Is::int()->map(Album\Artwork\Width::of(...)),
                                        )
                                        ->optional(
                                            'height',
                                            Is::int()->map(Album\Artwork\Height::of(...)),
                                        )
                                        ->map(static fn($artwork) => Album\Artwork::of(
                                            Maybe::of($artwork['width'] ?? null),
                                            Maybe::of($artwork['height'] ?? null),
                                            $artwork['url'],
                                        )),
                                ),
                        )
                        ->with('relationships', Is::shape(
                            'artists',
                            Is::shape(
                                'data',
                                Is::list(
                                    Is::shape(
                                        'id',
                                        Is::string()->map(Artist\Id::of(...)),
                                    )->map(static fn($artist): mixed => $artist['id']),
                                )->map(static fn($values) => Set::of(...$values)),
                            ),
                        ))
                        ->map(static fn($album) => Album::of(
                            $album['id'],
                            $album['attributes']['name'],
                            Maybe::of($album['attributes']['artwork'] ?? null),
                            $album['relationships']['artists']['data'],
                        )),
                )->map(static fn($albums) => Sequence::of(...$albums)),
            )
                ->optional(
                    'next',
                    Is::string()->map(
                        static fn($next) => Url::of($next.'&include=artists'),
                    ),
                );

            do {
                [$albums, $url] = $this
                    ->get($url)
                    ->flatMap(static fn($response) => $validate($response)->maybe())
                    ->match(
                        static fn($response) => [$response['data'], $response['next'] ?? null],
                        static fn() => [Sequence::of(), null],
                    );

                yield $albums;
            } while ($url instanceof Url);
        })->flatMap(static fn($albums) => $albums);
    }

    /**
     * @return Sequence<Song>
     */
    public function songs(Album\Id $album): Sequence
    {
        return Sequence::lazy(function() use ($album) {
            $url = $this->url("albums/{$album->toString()}/tracks?include=albums,artists");
            /**
             * @psalm-suppress MixedArrayAccess
             * @psalm-suppress MixedArgument
             * @psalm-suppress MixedArgumentTypeCoercion
             * @psalm-suppress InvalidArgument
             * @var Constraint<mixed, array{data: Sequence<Song>, next?: Url}>
             */
            $validate = Is::shape(
                'data',
                Is::list(
                    Is::shape(
                        'id',
                        Is::string()->map(Song\Id::of(...)),
                    )
                        ->with(
                            'attributes',
                            Is::shape(
                                'name',
                                Is::string()->map(Song\Name::of(...)),
                            )
                                ->optional(
                                    'durationInMillis',
                                    Is::int()->map(Song\Duration::of(...)),
                                )
                                ->optional(
                                    'trackNumber',
                                    Is::int()->map(Song\TrackNumber::of(...)),
                                )
                                ->with(
                                    'genreNames',
                                    Is::list(
                                        Is::string()->map(Song\Genre::of(...)),
                                    )->map(static fn($values) => Set::of(...$values)),
                                ),
                        )
                        ->with(
                            'relationships',
                            Is::shape(
                                'albums',
                                Is::shape(
                                    'data',
                                    Is::list(
                                        Is::shape(
                                            'id',
                                            Is::string()->map(Album\Id::of(...)),
                                        )->map(static fn($album): mixed => $album['id']),
                                    )->map(static fn($ids) => Set::of(...$ids)),
                                ),
                            )
                                ->with(
                                    'artists',
                                    Is::shape(
                                        'data',
                                        Is::list(
                                            Is::shape(
                                                'id',
                                                Is::string()->map(Artist\Id::of(...)),
                                            )->map(static fn($artist): mixed => $artist['id']),
                                        )->map(static fn($ids) => Set::of(...$ids)),
                                    ),
                                ),
                        )
                        ->map(static fn($song) => Song::of(
                            $song['id'],
                            $song['attributes']['name'],
                            Maybe::of($song['attributes']['durationInMillis'] ?? null),
                            Maybe::of($song['attributes']['trackNumber'] ?? null),
                            $song['attributes']['genreNames'],
                            $song['relationships']['albums']['data'],
                            $song['relationships']['artists']['data'],
                        )),
                )->map(static fn($songs) => Sequence::of(...$songs)),
            )
                ->optional(
                    'next',
                    Is::string()->map(
                        static fn($next) => Url::of($next.'&include=albums,artists'),
                    ),
                );

            do {
                [$songs, $url] = $this
                    ->get($url)
                    ->flatMap(static fn($response) => $validate($response)->maybe())
                    ->match(
                        static fn($response) => [$response['data'], $response['next'] ?? null],
                        static fn() => [Sequence::of(), null],
                    );

                yield $songs;
            } while ($url instanceof Url);
        })->flatMap(static fn($songs) => $songs);
    }

    /**
     * @return Maybe<array>
     */
    private function get(Url $url): Maybe
    {
        /** @var Maybe<array> */
        return ($this->fulfill)(Request::of(
            $url,
            Method::get,
            ProtocolVersion::v20,
            Headers::of(
                $this->authorization,
                $this->userToken,
            ),
        ))
            ->map(static fn($response) => $response->body()->toString())
            ->flatMap(Json::maybeDecode(...));
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/me/library/$path");
    }

    /**
     * @return Maybe<Storefront>
     */
    private static function decoreStorefronts(Response $response): Maybe
    {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedArgument
         * @var Constraint<mixed, Maybe<Storefront>>
         */
        $validate = Is::shape(
            'data',
            Is::list(
                Is::shape(
                    'id',
                    Is::string()->map(Storefront\Id::of(...)),
                )
                    ->with(
                        'attributes',
                        Is::shape(
                            'name',
                            Is::string()->map(Storefront\Name::of(...)),
                        )
                            ->with(
                                'defaultLanguageTag',
                                Is::string()->map(Storefront\Language::of(...)),
                            )
                            ->with(
                                'supportedLanguageTags',
                                Is::list(
                                    Is::string()->map(Storefront\Language::of(...)),
                                )->map(static fn($values) => Set::of(...$values)),
                            ),
                    )
                    ->map(static fn($storefront) => Storefront::of(
                        $storefront['id'],
                        $storefront['attributes']['name'],
                        $storefront['attributes']['defaultLanguageTag'],
                        $storefront['attributes']['supportedLanguageTags'],
                    )),
            )
                ->map(static fn($storefronts) => Sequence::of(...$storefronts))
                ->map(static fn($storefronts) => $storefronts->first()),
        )->map(static fn($response): mixed => $response['data']);

        return Json::maybeDecode($response->body()->toString())
            ->flatMap(static fn($response) => $validate($response)->maybe())
            ->flatMap(static fn($storefront) => $storefront);
    }
}
