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
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\Http\{
    Header,
    Request,
    Method,
    ProtocolVersion,
    Headers,
};
use Innmind\Url\Url;
use Innmind\Colour\RGBA;
use Innmind\Validation\{
    Constraint,
    Is,
};
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

    /**
     * @return Maybe<Artist>
     */
    public function artist(Artist\Id $id): Maybe
    {
        return $this
            ->get($this->url("artists/{$id->toString()}?include=albums"))
            ->flatMap($this->decodeArtists(...))
            ->flatMap(static fn($artists) => $artists->first());
    }

    /**
     * @return Maybe<Album>
     */
    public function album(Album\Id $id): Maybe
    {
        return $this
            ->get($this->url("albums/{$id->toString()}?include=artists,tracks"))
            ->flatMap($this->decodeAlbums(...))
            ->flatMap(static fn($albums) => $albums->first());
    }

    /**
     * @return Maybe<Song>
     */
    public function song(Song\Id $id): Maybe
    {
        return $this
            ->get($this->url("songs/{$id->toString()}?include=artists,albums"))
            ->flatMap($this->decodeSongs(...))
            ->flatMap(static fn($songs) => $songs->first());
    }

    /**
     * @return Sequence<Genre>
     */
    public function genres(): Sequence
    {
        return Sequence::lazy(function() {
            $url = $this->url('genres');
            /**
             * @psalm-suppress MixedArrayAccess
             * @var Constraint<mixed, array{data: Sequence<Genre>, next?: Url}>
             */
            $validate = Is::shape(
                'data',
                Is::list(
                    Is::shape(
                        'attributes',
                        Is::shape(
                            'name',
                            Is::string()->map(Genre::of(...)),
                        ),
                    )->map(static fn($genre): mixed => $genre['attributes']['name']),
                )->map(static fn($genres) => Sequence::of(...$genres)),
            )
                ->optional(
                    'next',
                    Is::string()->map(Url::of(...)),
                );

            do {
                [$genres, $url] = $this
                    ->get($url)
                    ->flatMap(Json::maybeDecode(...))
                    ->flatMap(static fn($response) => $validate($response)->maybe())
                    ->match(
                        static fn($response) => [$response['data'], $response['next'] ?? null],
                        static fn() => [Sequence::of(), null],
                    );

                yield $genres;
            } while ($url instanceof Url);
        })->flatMap(static fn($genres) => $genres);
    }

    public function search(string $term): Search
    {
        /**
         * @psalm-suppress MixedArrayAccess
         * @var Constraint<
         *      mixed,
         *      array{
         *          artists?: array{
         *              data: Sequence<Artist\Id>,
         *              next?: Url,
         *          },
         *          albums?: array{
         *              data: Sequence<Album\Id>,
         *              next?: Url,
         *          },
         *          songs?: array{
         *              data: Sequence<Song\Id>,
         *              next?: Url,
         *          },
         *      }
         * >
         */
        $validate = Is::shape(
            'results',
            Is::shape(
                'artists',
                Is::shape(
                    'data',
                    Is::list(
                        Is::shape(
                            'id',
                            Is::string()
                                ->map(static fn($id) => (int) $id)
                                ->map(Artist\Id::of(...)),
                        )->map(static fn($artist): mixed => $artist['id']),
                    )->map(static fn($artists) => Sequence::of(...$artists)),
                )
                    ->optional(
                        'next',
                        Is::string()->map(Url::of(...)),
                    ),
            )
                ->optional('artists')
                ->optional(
                    'albums',
                    Is::shape(
                        'data',
                        Is::list(
                            Is::shape(
                                'id',
                                Is::string()
                                    ->map(static fn($id) => (int) $id)
                                    ->map(Album\Id::of(...)),
                            )->map(static fn($album): mixed => $album['id']),
                        )->map(static fn($albums) => Sequence::of(...$albums)),
                    )
                        ->optional(
                            'next',
                            Is::string()->map(Url::of(...)),
                        ),
                )
                ->optional(
                    'songs',
                    Is::shape(
                        'data',
                        Is::list(
                            Is::shape(
                                'id',
                                Is::string()
                                    ->map(static fn($id) => (int) $id)
                                    ->map(Song\Id::of(...)),
                            )->map(static fn($song): mixed => $song['id']),
                        )->map(static fn($songs) => Sequence::of(...$songs)),
                    )
                        ->optional(
                            'next',
                            Is::string()->map(Url::of(...)),
                        ),
                ),
        )->map(static fn($response): mixed => $response['results']);
        $encodedTerm = \urlencode($term);
        $url = $this->url("search?term=$encodedTerm&types=artists,albums,songs&limit=25");
        $response = $this
            ->get($url)
            ->flatMap(Json::maybeDecode(...))
            ->flatMap(static fn($response) => $validate($response)->maybe())
            ->match(
                static fn($response) => $response,
                static fn() => [],
            );

        $artists = Sequence::lazy(
            function() use ($response, $validate): \Generator {
                do {
                    yield $response['artists']['data'] ?? Sequence::of();

                    $response = Maybe::of($response['artists']['next'] ?? null)
                        ->flatMap($this->get(...))
                        ->flatMap(Json::maybeDecode(...))
                        ->flatMap(static fn($response) => $validate($response)->maybe())
                        ->match(
                            static fn($response) => $response,
                            static fn() => null,
                        );
                } while (\is_array($response));
            },
        )->flatMap(static fn($artists) => $artists);

        $albums = Sequence::lazy(
            function() use ($response, $validate): \Generator {
                do {
                    yield $response['albums']['data'] ?? Sequence::of();

                    $response = Maybe::of($response['albums']['next'] ?? null)
                        ->flatMap($this->get(...))
                        ->flatMap(Json::maybeDecode(...))
                        ->flatMap(static fn($response) => $validate($response)->maybe())
                        ->match(
                            static fn($response) => $response,
                            static fn() => null,
                        );
                } while (\is_array($response));
            },
        )->flatMap(static fn($albums) => $albums);

        $songs = Sequence::lazy(
            function() use ($response, $validate): \Generator {
                do {
                    yield $response['songs']['data'] ?? Sequence::of();

                    $response = Maybe::of($response['songs']['next'] ?? null)
                        ->flatMap($this->get(...))
                        ->flatMap(Json::maybeDecode(...))
                        ->flatMap(static fn($response) => $validate($response)->maybe())
                        ->match(
                            static fn($response) => $response,
                            static fn() => null,
                        );
                } while (\is_array($response));
            },
        )->flatMap(static fn($songs) => $songs);

        return Search::of($term, $artists, $albums, $songs);
    }

    /**
     * @return Maybe<string>
     */
    private function get(Url $url): Maybe
    {
        return ($this->fulfill)(Request::of(
            $url,
            Method::get,
            ProtocolVersion::v20,
            Headers::of($this->authorization),
        ))->map(static fn($response) => $response->body()->toString());
    }

    /**
     * @return Maybe<Sequence<Artist>>
     */
    private function decodeArtists(string $content): Maybe
    {
        /** @var Constraint<mixed, array{data: Sequence<Album\Id>, next?: Url}> */
        $validateAlbums = Is::shape(
            'data',
            Is::list(
                Is::shape(
                    'id',
                    Is::string()
                        ->map(static fn($id) => (int) $id)
                        ->map(Album\Id::of(...)),
                )->map(static fn($album): mixed => $album['id']),
            )->map(static fn($ids) => Sequence::of(...$ids)),
        )
            ->optional(
                'next',
                Is::string()->map(Url::of(...)),
            );

        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedArgument
         * @psalm-suppress MixedArgumentTypeCoercion
         * @psalm-suppress InvalidArgument
         * @var Constraint<mixed, Sequence<Artist>>
         */
        $validate = Is::shape(
            'data',
            Is::list(
                Is::shape(
                    'attributes',
                    Is::shape(
                        'name',
                        Is::string()->map(Artist\Name::of(...)),
                    )
                        ->with(
                            'url',
                            Is::string()->map(Url::of(...)),
                        )
                        ->with(
                            'genreNames',
                            Is::list(
                                Is::string()->map(Genre::of(...)),
                            )->map(static fn($genres) => Set::of(...$genres)),
                        )
                        ->optional(
                            'artwork',
                            Is::shape(
                                'url',
                                Is::string()->map(Url::of(...)),
                            )
                                ->with(
                                    'height',
                                    Is::int()->map(Artwork\Height::of(...)),
                                )
                                ->with(
                                    'width',
                                    Is::int()->map(Artwork\Width::of(...)),
                                )
                                ->optional(
                                    'bgColor',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor1',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor2',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor3',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor4',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->map(static fn($artwork) => Artwork::of(
                                    $artwork['width'],
                                    $artwork['height'],
                                    $artwork['url'],
                                    Maybe::of($artwork['bgColor'] ?? null),
                                    Maybe::of($artwork['textColor1'] ?? null),
                                    Maybe::of($artwork['textColor2'] ?? null),
                                    Maybe::of($artwork['textColor3'] ?? null),
                                    Maybe::of($artwork['textColor4'] ?? null),
                                )),
                        ),
                )
                    ->with(
                        'id',
                        Is::string()
                            ->map(static fn($id) => (int) $id)
                            ->map(Artist\Id::of(...)),
                    )
                    ->with(
                        'relationships',
                        Is::shape(
                            'albums',
                            $validateAlbums->map(
                                fn($response) => Sequence::lazy(function() use ($response, $validateAlbums) {
                                    do {
                                        yield $response['data'];

                                        $response = Maybe::of($response['next'] ?? null)
                                            ->flatMap($this->get(...))
                                            ->flatMap(Json::maybeDecode(...))
                                            ->flatMap(static fn($response) => $validateAlbums($response)->maybe())
                                            ->match(
                                                static fn($response) => $response,
                                                static fn() => ['data' => Sequence::of()],
                                            );
                                    } while (!$response['data']->empty());
                                })
                                    ->flatMap(static fn($albums) => $albums)
                                    ->toSet(),
                            ),
                        ),
                    )
                    ->map(static fn($artist) => Artist::of(
                        $artist['id'],
                        $artist['attributes']['name'],
                        $artist['attributes']['url'],
                        $artist['attributes']['genreNames'],
                        $artist['relationships']['albums'],
                        Maybe::of($artist['attributes']['artwork'] ?? null),
                    )),
            )->map(static fn($artists) => Sequence::of(...$artists)),
        )->map(static fn($response): mixed => $response['data']);

        return Json::maybeDecode($content)->flatMap(
            static fn($response) => $validate($response)->maybe(),
        );
    }

    /**
     * @return Maybe<Sequence<Album>>
     */
    private function decodeAlbums(string $content): Maybe
    {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedArgument
         * @psalm-suppress MixedArgumentTypeCoercion
         * @psalm-suppress InvalidArgument
         * @var Constraint<mixed, Sequence<Album>>
         */
        $validate = Is::shape(
            'data',
            Is::list(
                Is::shape(
                    'id',
                    Is::string()
                        ->map(static fn($id) => (int) $id)
                        ->map(Album\Id::of(...)),
                )
                    ->with(
                        'attributes',
                        Is::shape(
                            'artwork',
                            Is::shape(
                                'width',
                                Is::int()->map(Artwork\Width::of(...)),
                            )
                                ->with(
                                    'height',
                                    Is::int()->map(Artwork\Height::of(...)),
                                )
                                ->with(
                                    'url',
                                    Is::string()->map(Url::of(...)),
                                )
                                ->optional(
                                    'bgColor',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor1',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor2',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor3',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->optional(
                                    'textColor4',
                                    Is::string()->map(RGBA::of(...)),
                                )
                                ->map(static fn($artwork) => Artwork::of(
                                    $artwork['width'],
                                    $artwork['height'],
                                    $artwork['url'],
                                    Maybe::of($artwork['bgColor'] ?? null),
                                    Maybe::of($artwork['textColor1'] ?? null),
                                    Maybe::of($artwork['textColor2'] ?? null),
                                    Maybe::of($artwork['textColor3'] ?? null),
                                    Maybe::of($artwork['textColor4'] ?? null),
                                )),
                        )
                            ->with(
                                'name',
                                Is::string()->map(Album\Name::of(...)),
                            )
                            ->with(
                                'isSingle',
                                Is::bool(),
                            )
                            ->with(
                                'url',
                                Is::string()->map(Url::of(...)),
                            )
                            ->with(
                                'isComplete',
                                Is::bool(),
                            )
                            ->with(
                                'genreNames',
                                Is::list(
                                    Is::string()->map(Genre::of(...)),
                                )->map(static fn($genres) => Set::of(...$genres)),
                            )
                            ->with(
                                'isMasteredForItunes',
                                Is::bool(),
                            )
                            ->optional(
                                'releaseDate',
                                Is::string()->map($this->releaseDate(...)),
                            )
                            ->optional(
                                'recordLabel',
                                Is::string(),
                            )
                            ->optional(
                                'copyright',
                                Is::string(),
                            )
                            ->optional(
                                'editorialNotes',
                                Is::shape('standard', Is::string())
                                    ->optional('standard')
                                    ->optional('short', Is::string()),
                            ),
                    )
                    ->with(
                        'relationships',
                        Is::shape(
                            'tracks',
                            Is::shape(
                                'data',
                                Is::list(
                                    Is::shape(
                                        'id',
                                        Is::string()
                                            ->map(static fn($id) => (int) $id)
                                            ->map(Song\Id::of(...)),
                                    )->map(static fn($song): mixed => $song['id']),
                                )->map(static fn($songs) => Set::of(...$songs)),
                            ),
                        )
                            ->with(
                                'artists',
                                Is::shape(
                                    'data',
                                    Is::list(
                                        Is::shape(
                                            'id',
                                            Is::string()
                                                ->map(static fn($id) => (int) $id)
                                                ->map(Artist\Id::of(...)),
                                        )->map(static fn($artist): mixed => $artist['id']),
                                    )->map(static fn($artists) => Set::of(...$artists)),
                                ),
                            ),
                    )
                    ->map(static fn($album) => Album::of(
                        $album['id'],
                        $album['attributes']['artwork'],
                        $album['attributes']['name'],
                        $album['attributes']['isSingle'],
                        $album['attributes']['url'],
                        $album['attributes']['isComplete'],
                        $album['attributes']['genreNames'],
                        $album['relationships']['tracks']['data'],
                        $album['attributes']['isMasteredForItunes'],
                        Maybe::of($album['attributes']['releaseDate'] ?? null)->flatMap(
                            static fn($point): mixed => $point,
                        ),
                        Album\RecordLabel::of($album['attributes']['recordLabel'] ?? ''),
                        Album\Copyright::of($album['attributes']['copyright'] ?? ''),
                        Album\EditorialNotes::of(
                            $album['attributes']['editorialNotes']['standard'] ?? '',
                            $album['attributes']['editorialNotes']['short'] ?? '',
                        ),
                        $album['relationships']['artists']['data'],
                    )),
            )->map(static fn($albums) => Sequence::of(...$albums)),
        )->map(static fn($response): mixed => $response['data']);

        return Json::maybeDecode($content)->flatMap(
            static fn($response) => $validate($response)->maybe(),
        );
    }

    /**
     * @return Maybe<Sequence<Song>>
     */
    private function decodeSongs(string $content): Maybe
    {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedArgument
         * @psalm-suppress MixedArgumentTypeCoercion
         * @psalm-suppress InvalidArgument
         * @var Constraint<mixed, Sequence<Song>>
         */
        $validate = Is::shape(
            'data',
            Is::list(
                Is::shape(
                    'id',
                    Is::string()
                        ->map(static fn($id) => (int) $id)
                        ->map(Song\Id::of(...)),
                )
                    ->with(
                        'attributes',
                        Is::shape(
                            'previews',
                            Is::list(
                                Is::shape(
                                    'url',
                                    Is::string()->map(Url::of(...)),
                                )->map(static fn($preview): mixed => $preview['url']),
                            )->map(static fn($urls) => Set::of(...$urls)),
                        )
                            ->with(
                                'artwork',
                                Is::shape(
                                    'width',
                                    Is::int()->map(Artwork\Width::of(...)),
                                )
                                    ->with(
                                        'height',
                                        Is::int()->map(Artwork\Height::of(...)),
                                    )
                                    ->with(
                                        'url',
                                        Is::string()->map(Url::of(...)),
                                    )
                                    ->optional(
                                        'bgColor',
                                        Is::string()->map(RGBA::of(...)),
                                    )
                                    ->optional(
                                        'textColor1',
                                        Is::string()->map(RGBA::of(...)),
                                    )
                                    ->optional(
                                        'textColor2',
                                        Is::string()->map(RGBA::of(...)),
                                    )
                                    ->optional(
                                        'textColor3',
                                        Is::string()->map(RGBA::of(...)),
                                    )
                                    ->optional(
                                        'textColor4',
                                        Is::string()->map(RGBA::of(...)),
                                    )
                                    ->map(static fn($artwork) => Artwork::of(
                                        $artwork['width'],
                                        $artwork['height'],
                                        $artwork['url'],
                                        Maybe::of($artwork['bgColor'] ?? null),
                                        Maybe::of($artwork['textColor1'] ?? null),
                                        Maybe::of($artwork['textColor2'] ?? null),
                                        Maybe::of($artwork['textColor3'] ?? null),
                                        Maybe::of($artwork['textColor4'] ?? null),
                                    )),
                            )
                            ->with(
                                'url',
                                Is::string()->map(Url::of(...)),
                            )
                            ->optional(
                                'discNumber',
                                Is::int()->map(Song\DiscNumber::of(...)),
                            )
                            ->with(
                                'genreNames',
                                Is::list(
                                    Is::string()->map(Genre::of(...)),
                                )->map(static fn($genres) => Set::of(...$genres)),
                            )
                            ->optional(
                                'durationInMillis',
                                Is::int()->map(Song\Duration::of(...)),
                            )
                            ->optional(
                                'releaseDate',
                                Is::string()->map($this->releaseDate(...)),
                            )
                            ->with(
                                'name',
                                Is::string()->map(Song\Name::of(...)),
                            )
                            ->optional(
                                'isrc',
                                Is::string()->map(Song\ISRC::of(...)),
                            )
                            ->optional(
                                'trackNumber',
                                Is::int()->map(Song\TrackNumber::of(...)),
                            )
                            ->optional(
                                'composerName',
                                Is::string(),
                            ),
                    )
                    ->with(
                        'relationships',
                        Is::shape(
                            'artists',
                            Is::shape(
                                'data',
                                Is::list(
                                    Is::shape(
                                        'id',
                                        Is::string()
                                            ->map(static fn($id) => (int) $id)
                                            ->map(Artist\Id::of(...)),
                                    )->map(static fn($artist): mixed => $artist['id']),
                                )->map(static fn($artists) => Set::of(...$artists)),
                            ),
                        )
                            ->with(
                                'albums',
                                Is::shape(
                                    'data',
                                    Is::list(
                                        Is::shape(
                                            'id',
                                            Is::string()
                                                ->map(static fn($id) => (int) $id)
                                                ->map(Album\Id::of(...)),
                                        )->map(static fn($album): mixed => $album['id']),
                                    )->map(static fn($albums) => Set::of(...$albums)),
                                ),
                            ),
                    )
                    ->map(static fn($song) => Song::of(
                        $song['id'],
                        $song['attributes']['previews'],
                        $song['attributes']['artwork'],
                        $song['attributes']['url'],
                        Maybe::of($song['attributes']['discNumber'] ?? null),
                        $song['attributes']['genreNames'],
                        Maybe::of($song['attributes']['durationInMillis'] ?? null),
                        Maybe::of($song['attributes']['releaseDate'] ?? null)->flatMap(
                            static fn($point): mixed => $point,
                        ),
                        $song['attributes']['name'],
                        Maybe::of($song['attributes']['isrc'] ?? null),
                        Maybe::of($song['attributes']['trackNumber'] ?? null),
                        Song\Composer::of($song['attributes']['composerName'] ?? ''),
                        $song['relationships']['artists']['data'],
                        $song['relationships']['albums']['data'],
                    )),
            )->map(static fn($songs) => Sequence::of(...$songs)),
        )->map(static fn($response): mixed => $response['data']);

        return Json::maybeDecode($content)->flatMap(
            static fn($response) => $validate($response)->maybe(),
        );
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/catalog/{$this->storefront->toString()}/$path");
    }

    /**
     * @return Maybe<PointInTime>
     */
    private function releaseDate(string $releaseDate): Maybe
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
