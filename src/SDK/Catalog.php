<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Catalog\{
    Artist,
    Album,
    Genre,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header\Authorization,
    Message\Request\Request,
    Message\Response,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
};
use Innmind\Url\{
    UrlInterface,
    Url,
};
use Innmind\Json\Json;
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Catalog
{
    private $fulfill;
    private $authorization;
    private $storefront;

    public function __construct(
        Transport $fulfill,
        Authorization $authorization,
        Storefront\Id $storefront
    ) {
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
        $this->storefront = $storefront;
    }

    public function artist(Artist\Id $id): Artist
    {
        $response = $this->get($this->url("artists/$id"));
        $resource = Json::decode((string) $response->body());

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
            $response = $this->get(Url::fromString($resources['next']));
            $resources = Json::decode((string) $response->body());

            $albums = $albums->merge($this->artistAlbums($resources));
        }

        return $albums;
    }

    private function get(UrlInterface $url): Response
    {
        return ($this->fulfill)(new Request(
            $url,
            Method::get(),
            new ProtocolVersion(2, 0),
            Headers::of($this->authorization)
        ));
    }

    private function url(string $path, string $query = null): UrlInterface
    {
        $query = \is_null($query) ? '' : "?$query";

        return Url::fromString("/v1/catalog/{$this->storefront}/$path$query");
    }
}
