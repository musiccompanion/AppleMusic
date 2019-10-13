<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\Library\Artist;
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Message\Request\Request,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
    Header,
    Header\Authorization,
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

final class Library
{
    private $fulfill;
    private $authorization;
    private $userToken;

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
        $resource = $this->get(Url::fromString('/v1/me/storefront'));

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
     * @return SetInterface<Artist>
     */
    public function artists(): SetInterface
    {
        $url = $this->url('artists');
        $artists = Set::of(Artist::class);

        do {
            $resource = $this->get($url);
            $url = null;

            foreach ($resource['data'] as $artist) {
                $artists = $artists->add(new Artist(
                    new Artist\Id($artist['id']),
                    new Artist\Name($artist['attributes']['name'])
                ));
            }

            if (\array_key_exists('next', $resource)) {
                $url = Url::fromString($resource['next']);
            }
        } while ($url instanceof UrlInterface);

        return $artists;
    }

    private function get(UrlInterface $url): array
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

        return Json::decode((string) $response->body());
    }

    private function url(string $path): UrlInterface
    {
        return Url::fromString("/v1/me/library/$path");
    }
}
