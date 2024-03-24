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
use Innmind\Validation\{
    Constraint,
    Is,
};
use Innmind\Json\Json;
use Innmind\Immutable\{
    Set,
    Maybe,
};

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
            ->flatMap($this->decode(...))
            ->toSequence()
            ->toSet()
            ->flatMap(static fn($storefronts) => $storefronts);
    }

    /**
     * @return Maybe<Set<Storefront>>
     */
    private function decode(Response $response): Maybe
    {
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedArgument
         * @var Constraint<mixed, Set<Storefront>>
         */
        $validate = Is::shape(
            'data',
            Is::list(
                Is::shape(
                    'id',
                    Is::string()->map(static fn($id) => new Storefront\Id($id)),
                )
                    ->with(
                        'attributes',
                        Is::shape(
                            'name',
                            Is::string()->map(static fn($name) => new Storefront\Name($name)),
                        )
                            ->with(
                                'defaultLanguageTag',
                                Is::string()->map(Storefront\Language::of(...)),
                            )
                            ->with(
                                'supportedLanguageTags',
                                Is::list(
                                    Is::string()->map(Storefront\Language::of(...)),
                                )->map(static fn($values) => Set::of(...$values)),
                            ),
                    )
                    ->map(static fn($shape) => new Storefront(
                        $shape['id'],
                        $shape['attributes']['name'],
                        $shape['attributes']['defaultLanguageTag'],
                        $shape['attributes']['supportedLanguageTags'],
                    )),
            )->map(static fn($values) => Set::of(...$values)),
        )->map(static fn($response): mixed => $response['data']);

        return Json::maybeDecode($response->body()->toString())->flatMap(
            static fn($response) => $validate($response)->maybe(),
        );
    }
}
