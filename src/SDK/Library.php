<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Library\{
    Artist,
    Album,
    Song,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Message\Request\Request,
    Message\Method,
    ProtocolVersion,
    Headers,
    Header,
    Header\Authorization,
};
use Innmind\Url\Url;
use Innmind\Json\Json;
use Innmind\Immutable\Set;

final class Library
{
    private Transport $fulfill;
    private Authorization $authorization;
    private Header $userToken;

    public function __construct(
        Transport $fulfill,
        Authorization $authorization,
        Header $userToken
    ) {
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
        $this->userToken = $userToken;
    }

    public function storefront(): Storefront
    {
        $resource = $this->get(Url::of('/v1/me/storefront'));

        return new Storefront(
            new Storefront\Id($resource['data'][0]['id']),
            new Storefront\Name($resource['data'][0]['attributes']['name']),
            new Storefront\Language($resource['data'][0]['attributes']['defaultLanguageTag']),
            ...\array_map(
                static function(string $language): Storefront\Language {
                    return new Storefront\Language($language);
                },
                $resource['data'][0]['attributes']['supportedLanguageTags']
            )
        );
    }

    /**
     * @return Set<Artist>
     */
    public function artists(): Set
    {
        $url = $this->url('artists');

        return Set::lazy(
            Artist::class,
            function() use ($url) {
                do {
                    $resource = $this->get($url);
                    $url = null;

                    foreach ($resource['data'] as $artist) {
                        yield new Artist(
                            new Artist\Id($artist['id']),
                            new Artist\Name($artist['attributes']['name'])
                        );
                    }

                    if (\array_key_exists('next', $resource)) {
                        $url = Url::of($resource['next']);
                    }
                } while ($url instanceof Url);
            }
        );
    }

    /**
     * @return Set<Album>
     */
    public function albums(Artist\Id $artist): Set
    {
        $url = $this->url("artists/$artist/albums?include=artists");
        $albums = Set::of(Album::class);

        do {
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $album) {
                $albums = $albums->add(new Album(
                    new Album\Id($album['id']),
                    new Album\Name($album['attributes']['name']),
                    \array_key_exists('artwork', $album['attributes']) ? new Album\Artwork(
                        new Album\Artwork\Width($album['attributes']['artwork']['width']),
                        new Album\Artwork\Height($album['attributes']['artwork']['height']),
                        Url::of($album['attributes']['artwork']['url'])
                    ) : null,
                    ...\array_reduce(
                        $album['relationships']['artists']['data'],
                        static function(array $artists, array $artist): array {
                            $artists[] = new Artist\Id($artist['id']);

                            return $artists;
                        },
                        []
                    )
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
        $url = $this->url("albums/$album/tracks?include=albums,artists");
        $songs = Set::of(Song::class);

        do {
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $song) {
                $songs = $songs->add(new Song(
                    new Song\Id($song['id']),
                    new Song\Name($song['attributes']['name']),
                    new Song\Duration($song['attributes']['durationInMillis']),
                    new Song\TrackNumber($song['attributes']['trackNumber']),
                    Set::of(
                        Song\Genre::class,
                        ...\array_reduce(
                            $song['attributes']['genreNames'],
                            static function(array $genres, string $genre): array {
                                $genres[] = new Song\Genre($genre);

                                return $genres;
                            },
                            []
                        )
                    ),
                    Set::of(
                        Album\Id::class,
                        ...\array_reduce(
                            $song['relationships']['albums']['data'],
                            static function(array $albums, array $album): array {
                                $albums[] = new Album\Id($album['id']);

                                return $albums;
                            },
                            []
                        )
                    ),
                    Set::of(
                        Artist\Id::class,
                        ...\array_reduce(
                            $song['relationships']['artists']['data'],
                            static function(array $artists, array $artist): array {
                                $artists[] = new Artist\Id($artist['id']);

                                return $artists;
                            },
                            []
                        )
                    )
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
        $response = ($this->fulfill)(new Request(
            $url,
            Method::get(),
            new ProtocolVersion(2, 0),
            Headers::of(
                $this->authorization,
                $this->userToken
            )
        ));

        return Json::decode($response->body()->toString());
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/me/library/$path");
    }
}
