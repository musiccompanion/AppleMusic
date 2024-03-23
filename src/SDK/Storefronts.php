<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\Http\{
    Header,
    Request,
    Response,
    Method,
    ProtocolVersion,
    Headers,
};
use Innmind\Url\Url;
use Innmind\Json\Json;
use Innmind\Immutable\Set;

final class Storefronts
{
    private HttpTransport $fulfill;
    private Header $authorization;

    public function __construct(HttpTransport $fulfill, Header $authorization)
    {
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
    }

    /**
     * @return Set<Storefront>
     */
    public function all(): Set
    {
        return ($this->fulfill)(Request::of(
            Url::of('/v1/storefronts'),
            Method::get,
            ProtocolVersion::v20,
            Headers::of(
                $this->authorization,
            ),
        ))
            ->map($this->decode(...))
            ->map(static fn($storefronts) => Set::of(...$storefronts['data']))
            ->map(static fn($storefronts) => $storefronts->map(
                static fn($storefront) => new Storefront(
                    new Storefront\Id($storefront['id']),
                    new Storefront\Name($storefront['attributes']['name']),
                    Storefront\Language::of($storefront['attributes']['defaultLanguageTag']),
                    Set::of(...$storefront['attributes']['supportedLanguageTags'])->map(Storefront\Language::of(...)),
                ),
            ))
            ->match(
                static fn($storefronts) => $storefronts,
                static fn() => Set::of(),
            );
    }

    /**
     * @return array{
     *     data: list<array{
     *         id: string,
     *         attributes: array{
     *             name: string,
     *             defaultLanguageTag: string,
     *             supportedLanguageTags: list<string>
     *         }
     *     }>
     * }
     */
    private function decode(Response $response): array
    {
        /**
         * @var array{
         *     data: list<array{
         *         id: string,
         *         attributes: array{
         *             name: string,
         *             defaultLanguageTag: string,
         *             supportedLanguageTags: list<string>
         *         }
         *     }>
         * }
         */
        return Json::decode($response->body()->toString());
    }
}
