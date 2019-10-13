<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist,
    Album,
    Genre,
    Song,
    Artwork,
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header\Authorization,
    Message\Request\Request,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
};
use Innmind\Url\{
    UrlInterface,
    Url,
};
use Innmind\Colour\RGBA;
use Innmind\Json\Json;
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Catalog
{
    private $clock;
    private $fulfill;
    private $authorization;
    private $storefront;

    public function __construct(
        TimeContinuumInterface $clock,
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
            Url::fromString($resource['data'][0]['attributes']['url']),
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
                Url::fromString($resource['data'][0]['attributes']['artwork']['url']),
                RGBA::fromString($resource['data'][0]['attributes']['artwork']['bgColor']),
                RGBA::fromString($resource['data'][0]['attributes']['artwork']['textColor1']),
                RGBA::fromString($resource['data'][0]['attributes']['artwork']['textColor2']),
                RGBA::fromString($resource['data'][0]['attributes']['artwork']['textColor3']),
                RGBA::fromString($resource['data'][0]['attributes']['artwork']['textColor4'])
            ),
            new Album\Name($resource['data'][0]['attributes']['name']),
            $resource['data'][0]['attributes']['isSingle'],
            Url::fromString($resource['data'][0]['attributes']['url']),
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

    /**
     * @return SetInterface<Album\Id>
     */
    private function artistAlbums(array $resources): SetInterface
    {
        $albums = Set::of(Album\Id::class);

        foreach ($resources['data'] as $album) {
            $albums = $albums->add(new Album\Id((int) $album['id']));
        }

        if (\array_key_exists('next', $resources)) {
            $resources = $this->get(Url::fromString($resources['next']));

            $albums = $albums->merge($this->artistAlbums($resources));
        }

        return $albums;
    }

    private function get(UrlInterface $url): array
    {
        $response = ($this->fulfill)(new Request(
            $url,
            Method::get(),
            new ProtocolVersion(2, 0),
            Headers::of($this->authorization)
        ));

        return Json::decode((string) $response->body());
    }

    private function url(string $path, string $query = null): UrlInterface
    {
        $query = \is_null($query) ? '' : "?$query";

        return Url::fromString("/v1/catalog/{$this->storefront}/$path$query");
    }
}
