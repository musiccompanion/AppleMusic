<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header\Authorization,
    Message\Request\Request,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
    Headers\Headers,
};
use Innmind\Url\Url;
use Innmind\Json\Json;
use Innmind\Immutable\{
    SetInterface,
    Set,
};

final class Storefronts
{
    private Transport $fulfill;
    private Authorization $authorization;

    public function __construct(Transport $fulfill, Authorization $authorization)
    {
        $this->fulfill = $fulfill;
        $this->authorization = $authorization;
    }

    /**
     * @return SetInterface<Storefront>
     */
    public function all(): SetInterface
    {
        $response = ($this->fulfill)(new Request(
            Url::fromString('/v1/storefronts'),
            Method::get(),
            new ProtocolVersion(2, 0),
            Headers::of(
                $this->authorization
            )
        ));

        $resource = Json::decode((string) $response->body());
        $storefronts = Set::of(Storefront::class);

        foreach ($resource['data'] as $storefront) {
            $storefronts = $storefronts->add(new Storefront(
                new Storefront\Id($storefront['id']),
                new Storefront\Name($storefront['attributes']['name']),
                new Storefront\Language($storefront['attributes']['defaultLanguageTag']),
                ...\array_map(
                    static function(string $language): Storefront\Language {
                        return new Storefront\Language($language);
                    },
                    $storefront['attributes']['supportedLanguageTags']
                )
            ));
        }

        return $storefronts;
    }
}
