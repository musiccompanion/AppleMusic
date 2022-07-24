<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic;

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
use Innmind\Immutable\Maybe;
use Lcobucci\JWT\{
    Configuration,
    Signer\Ecdsa\Sha256,
    Signer\Key\InMemory,
};

final class SDK
{
    private SDK\HttpTransport $transport;
    private Clock $clock;
    private Header $authorization;
    private string $jwt;

    public function __construct(
        Clock $clock,
        Transport $transport,
        Key $key,
        Period $tokenValidity,
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
        $this->transport = new SDK\HttpTransport($transport);
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

    public function storefronts(): SDK\Storefronts
    {
        return new SDK\Storefronts($this->transport, $this->authorization);
    }

    /**
     * @return Maybe<SDK\Library>
     */
    public function library(string $userToken): Maybe
    {
        return SDK\Library::of(
            $this->transport,
            $this->authorization,
            new Header('Music-User-Token', new Value($userToken)),
        );
    }

    public function catalog(SDK\Storefront\Id $storefront): SDK\Catalog
    {
        return new SDK\Catalog(
            $this->clock,
            $this->transport,
            $this->authorization,
            $storefront,
        );
    }
}
