<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic;

use Innmind\Time\{
    Clock,
    Period,
    Format,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Header,
    Header\Authorization,
    Header\Value,
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
    private Authorization $authorization;
    private string $jwt;

    private function __construct(
        Clock $clock,
        Transport $transport,
        Key $key,
        Period $tokenValidity,
    ) {
        /** @psalm-suppress ArgumentTypeCoercion */
        $config = Configuration::forSymmetricSigner(
            new Sha256,
            InMemory::plainText($key->content()->toString()),
        );
        /** @psalm-suppress ArgumentTypeCoercion */
        $jwt = $config
            ->builder()
            ->withHeader('alg', 'ES256')
            ->withHeader('kid', $key->id())
            ->issuedBy($key->teamId())
            ->issuedAt(new \DateTimeImmutable(
                $clock->now()->format(Format::iso8601()),
            ))
            ->expiresAt(new \DateTimeImmutable(
                $clock->now()->goForward($tokenValidity)->format(Format::iso8601()),
            ))
            ->getToken(
                $config->signer(),
                $config->signingKey(),
            );

        $this->clock = $clock;
        $this->transport = new SDK\HttpTransport($transport);
        $this->jwt = $jwt->toString();
        $this->authorization = Authorization::of('Bearer', $this->jwt);
    }

    public static function of(
        Clock $clock,
        Transport $transport,
        Key $key,
        Period $tokenValidity,
    ): self {
        return new self($clock, $transport, $key, $tokenValidity);
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
            Header::of('Music-User-Token', Value::of($userToken)),
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
