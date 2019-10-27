<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic;

use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PeriodInterface,
};
use Innmind\HttpTransport\Transport;
use Innmind\Stream\Readable;
use Innmind\Http\Header\{
    Header,
    Value\Value,
    Authorization,
    AuthorizationValue,
};
use Lcobucci\JWT\{
    Builder,
    Signer\Ecdsa\Sha256,
    Signer,
};

final class SDK
{
    private $transport;
    private $clock;
    private $authorization;

    public function __construct(
        TimeContinuumInterface $clock,
        Transport $transport,
        Key $key,
        PeriodInterface $tokenValidity
    ) {
        $jwt = (new Builder)
            ->withHeader('alg', 'ES256')
            ->withHeader('kid', $key->id())
            ->withClaim('iss', $key->teamId())
            ->withClaim('iat', (int) ($clock->now()->milliseconds() / 1000))
            ->withClaim('exp', (int) ($clock->now()->goForward($tokenValidity)->milliseconds() / 1000))
            ->getToken(
                new Sha256,
                new Signer\Key((string) $key->content())
            );

        $this->clock = $clock;
        $this->transport = new SDK\HttpTransport\AppleMusic($transport);
        $this->authorization = new Authorization(
            new AuthorizationValue('Bearer', (string) $jwt)
        );
    }

    public function storefronts(): SDK\Storefronts
    {
        return new SDK\Storefronts($this->transport, $this->authorization);
    }

    public function library(string $userToken): SDK\Library
    {
        return new SDK\Library(
            $this->transport,
            $this->authorization,
            new Header('Music-User-Token', new Value($userToken))
        );
    }

    public function catalog(SDK\Storefront\Id $storefront): SDk\Catalog
    {
        return new SDK\Catalog(
            $this->clock,
            $this->transport,
            $this->authorization,
            $storefront
        );
    }
}
