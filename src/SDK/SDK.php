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
    Earth\Format\ISO8601,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\Header\{
    Header,
    Value\Value,
};
use Lcobucci\JWT\{
    Configuration,
    Signer\Ecdsa\Sha256,
    Signer\Key\InMemory,
};

final class SDK implements SDKInterface
{
    private Transport $transport;
    private Clock $clock;
    private Header $authorization;
    private string $jwt;

    public function __construct(
        Clock $clock,
        Transport $transport,
        Key $key,
        Period $tokenValidity
    ) {
        $config = Configuration::forSymmetricSigner(
            Sha256::create(),
            InMemory::plainText($key->content()->toString()),
        );
        $jwt = $config
            ->builder()
            ->withHeader('alg', 'ES256')
            ->withHeader('kid', $key->id())
            ->issuedBy($key->teamId())
            ->issuedAt(new \DateTimeImmutable(
                $clock->now()->format(new ISO8601),
            ))
            ->expiresAt(new \DateTimeImmutable(
                $clock->now()->goForward($tokenValidity)->format(new ISO8601),
            ))
            ->getToken(
                $config->signer(),
                $config->signingKey(),
            );

        $this->clock = $clock;
        $this->transport = new HttpTransport\AppleMusic($transport);
        $this->jwt = $jwt->toString();
        $this->authorization = new Header(
            'Authorization',
            new Value('Bearer '.$this->jwt),
        );
    }

    public function jwt(): string
    {
        return $this->jwt;
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
