<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

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
