<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\Http\{
    Header,
    Message\Request\Request,
    Message\Method,
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
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $response = ($this->fulfill)(new Request(
            Url::of('/v1/storefronts'),
            Method::get,
            ProtocolVersion::v20,
            Headers::of(
                $this->authorization,
            ),
        ))->match(
            static fn($response) => $response,
            static fn() => throw new \RuntimeException,
        );

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
        $resource = Json::decode($response->body()->toString());
        /** @var Set<Storefront> */
        $storefronts = Set::of();

        foreach ($resource['data'] as $storefront) {
            $storefronts = ($storefronts)(new Storefront(
                new Storefront\Id($storefront['id']),
                new Storefront\Name($storefront['attributes']['name']),
                new Storefront\Language($storefront['attributes']['defaultLanguageTag']),
                Set::of(...$storefront['attributes']['supportedLanguageTags'])->map(
                    static fn($language) => new Storefront\Language($language),
                ),
            ));
        }

        return $storefronts;
    }
}
