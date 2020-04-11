<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\{
    SDK as SDKInterface,
    Key,
};
use Innmind\TimeContinuum\{
    Clock,
    Period,
};
use Innmind\HttpTransport\Transport;
use Innmind\Stream\Readable;
use Innmind\Http\Header\{
    Header,
    Value\Value,
    Authorization,
};
use Lcobucci\JWT\{
    Builder,
    Signer\Ecdsa\Sha256,
    Signer,
};

final class SDK implements SDKInterface
{
    private Transport $transport;
    private Clock $clock;
    private Authorization $authorization;

    public function __construct(
        Clock $clock,
        Transport $transport,
        Key $key,
        Period $tokenValidity
    ) {
        $jwt = (new Builder)
            ->withHeader('alg', 'ES256')
            ->withHeader('kid', $key->id())
            ->withClaim('iss', $key->teamId())
            ->withClaim('iat', (int) ($clock->now()->milliseconds() / 1000))
            ->withClaim('exp', (int) ($clock->now()->goForward($tokenValidity)->milliseconds() / 1000))
            ->getToken(
                new Sha256,
                new Signer\Key($key->content()->toString()),
            );

        $this->clock = $clock;
        $this->transport = new HttpTransport\AppleMusic($transport);
        $this->authorization = Authorization::of('Bearer', (string) $jwt);
    }

    public function storefronts(): Storefronts
    {
        return new Storefronts\Storefronts($this->transport, $this->authorization);
    }

    public function library(string $userToken): Library
    {
        return new Library\Library(
            $this->transport,
            $this->authorization,
            new Header('Music-User-Token', new Value($userToken)),
        );
    }

    public function catalog(Storefront\Id $storefront): Catalog
    {
        return new Catalog\Catalog(
            $this->clock,
            $this->transport,
            $this->authorization,
            $storefront,
        );
    }
}
