<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist,
    Album,
    Genre,
    Song,
    Artwork,
    Search,
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
use Innmind\Immutable\Set;

final class Catalog
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
        $resource = $this->get($this->url("artists/$id"));

        return new Artist(
            $id,
            new Artist\Name($resource['data'][0]['attributes']['name']),
            Url::of($resource['data'][0]['attributes']['url']),
            Set::of(Genre::class, ...\array_map(
                static function(string $genre): Genre {
                    return new Genre($genre);
                },
                $resource['data'][0]['attributes']['genreNames']
            )),
            $this->artistAlbums($resource['data'][0]['relationships']['albums'])
        );
    }

    public function album(Album\Id $id): Album
    {
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
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor4'])
            ),
            new Album\Name($resource['data'][0]['attributes']['name']),
            $resource['data'][0]['attributes']['isSingle'],
            Url::of($resource['data'][0]['attributes']['url']),
            $resource['data'][0]['attributes']['isComplete'],
            Set::of(Genre::class, ...\array_map(
                static function(string $genre): Genre {
                    return new Genre($genre);
                },
                $resource['data'][0]['attributes']['genreNames']
            )),
            Set::of(Song\Id::class, ...\array_map(
                static function(array $song): Song\Id {
                    return new Song\Id((int) $song['id']);
                },
                $resource['data'][0]['relationships']['tracks']['data']
            )),
            $resource['data'][0]['attributes']['isMasteredForItunes'],
            $this->clock->at($resource['data'][0]['attributes']['releaseDate']),
            new Album\RecordLabel($resource['data'][0]['attributes']['recordLabel']),
            new Album\Copyright($resource['data'][0]['attributes']['copyright']),
            new Album\EditorialNotes(
                $resource['data'][0]['attributes']['editorialNotes']['standard'],
                $resource['data'][0]['attributes']['editorialNotes']['short']
            ),
            Set::of(Artist\Id::class, ...\array_map(
                static function(array $artist): Artist\Id {
                    return new Artist\Id((int) $artist['id']);
                },
                $resource['data'][0]['relationships']['artists']['data']
            ))
        );
    }

    public function song(Song\Id $id): Song
    {
        $resource = $this->get($this->url("songs/$id"));

        return new Song(
            $id,
            Set::of(Url::class, ...\array_map(
                static function(array $preview): Url {
                    return Url::of($preview['url']);
                },
                $resource['data'][0]['attributes']['previews']
            )),
            new Artwork(
                new Artwork\Width($resource['data'][0]['attributes']['artwork']['width']),
                new Artwork\Height($resource['data'][0]['attributes']['artwork']['height']),
                Url::of($resource['data'][0]['attributes']['artwork']['url']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['bgColor']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor1']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor2']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor3']),
                RGBA::of($resource['data'][0]['attributes']['artwork']['textColor4'])
            ),
            Url::of($resource['data'][0]['attributes']['url']),
            new Song\DiscNumber($resource['data'][0]['attributes']['discNumber']),
            Set::of(Genre::class, ...\array_map(
                static function(string $genre): Genre {
                    return new Genre($genre);
                },
                $resource['data'][0]['attributes']['genreNames']
            )),
            new Song\Duration($resource['data'][0]['attributes']['durationInMillis']),
            $this->clock->at($resource['data'][0]['attributes']['releaseDate']),
            new Song\Name($resource['data'][0]['attributes']['name']),
            new Song\ISRC($resource['data'][0]['attributes']['isrc']),
            new Song\TrackNumber($resource['data'][0]['attributes']['trackNumber']),
            new Song\Composer($resource['data'][0]['attributes']['composerName']),
            Set::of(Artist\Id::class, ...\array_map(
                static function(array $artist): Artist\Id {
                    return new Artist\Id((int) $artist['id']);
                },
                $resource['data'][0]['relationships']['artists']['data']
            )),
            Set::of(Album\Id::class, ...\array_map(
                static function(array $album): Album\Id {
                    return new Album\Id((int) $album['id']);
                },
                $resource['data'][0]['relationships']['albums']['data']
            ))
        );
    }

    /**
     * @return Set<Album\Id>
     */
    private function artistAlbums(array $resources): Set
    {
        $albums = Set::of(Album\Id::class);

        foreach ($resources['data'] as $album) {
            $albums = $albums->add(new Album\Id((int) $album['id']));
        }

        if (\array_key_exists('next', $resources)) {
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
        $genres = Set::of(Genre::class);

        do {
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $genre) {
                $genres = $genres->add(new Genre(
                    $genre['attributes']['name']
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
        $resource = $this->get($url);

        $artists = Set::lazy(
            Artist\Id::class,
            function() use ($resource) {
                do {
                    foreach ($resource['results']['artists']['data'] as $artist) {
                        yield new Artist\Id((int) $artist['id']);
                    }

                    if (!\array_key_exists('next', $resource['results']['artists'])) {
                        return;
                    }

                    $resource = $this->get(Url::of($resource['results']['artists']['next']));
                } while (true);
            }
        );

        $albums = Set::lazy(
            Album\Id::class,
            function() use ($resource) {
                do {
                    foreach ($resource['results']['albums']['data'] as $album) {
                        yield new Album\Id((int) $album['id']);
                    }

                    if (!\array_key_exists('next', $resource['results']['albums'])) {
                        return;
                    }

                    $resource = $this->get(Url::of($resource['results']['albums']['next']));
                } while (true);
            }
        );

        $songs = Set::lazy(
            Song\Id::class,
            function() use ($resource) {
                do {
                    foreach ($resource['results']['songs']['data'] as $song) {
                        yield new Song\Id((int) $song['id']);
                    }

                    if (!\array_key_exists('next', $resource['results']['songs'])) {
                        return;
                    }

                    $resource = $this->get(Url::of($resource['results']['songs']['next']));
                } while (true);
            }
        );

        return new Search($term, $artists, $albums, $songs);
    }

    private function get(Url $url): array
    {
        $response = ($this->fulfill)(new Request(
            $url,
            Method::get(),
            new ProtocolVersion(2, 0),
            Headers::of($this->authorization)
        ));

        return Json::decode($response->body()->toString());
    }

    private function url(string $path): Url
    {
        return Url::of("/v1/catalog/{$this->storefront}/$path");
    }
}
