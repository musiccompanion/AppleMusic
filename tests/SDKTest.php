<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\{
    SDK,
    SDK\Storefronts,
    SDK\Catalog,
    Key,
};
use Innmind\Time\{
    Clock,
    Point,
    Period,
};
use Innmind\HttpTransport\{
    Transport,
    Success,
};
use Innmind\Http\{
    Response,
    Response\StatusCode,
    ProtocolVersion,
};
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\Either;
use Lcobucci\JWT\{
    Token\Parser,
    Encoding\JoseEncoder,
};
use Fixtures\MusicCompanion\AppleMusic\SDK\Storefront;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class SDKTest extends TestCase
{
    use BlackBox;

    public function testInterface(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Storefront\Id::any(),
                Set\Strings::any(),
            )
            ->prove(function($storefront, $userToken) {
                $clock = Clock::frozen(Point::at(new \DateTimeImmutable('2019-01-01T00:00:00+00:00')));
                $transport = Transport::via(function($request) {
                    $header = $request
                        ->headers()
                        ->get('authorization')
                        ->flatMap(static fn($header) => $header->values()->find(static fn() => true))
                        ->match(
                            static fn($value) => $value->toString(),
                            static fn() => null,
                        );
                    $jwt = \substr($header, 7); // remove Bearer
                    $jwt = (new Parser(new JoseEncoder))->parse($jwt);

                    $this->assertSame('AAAAAAAAAA', $jwt->headers()->get('kid'));
                    $this->assertSame('BBBBBBBBBB', $jwt->claims()->get('iss'));
                    $this->assertSame('2019-01-01T00:00:00+00:00', $jwt->claims()->get('iat')->format(\DateTime::ATOM));
                    $this->assertSame('2019-01-01T00:01:00+00:00', $jwt->claims()->get('exp')->format(\DateTime::ATOM));

                    return Either::right(new Success(
                        $request,
                        Response::of(
                            StatusCode::ok,
                            ProtocolVersion::v11,
                            null,
                            Content::ofString('{"data":[]}'),
                        ),
                    ));
                });
                $key = Key::of(
                    'AAAAAAAAAA',
                    'BBBBBBBBBB',
                    // this is a randomly generated key
                    Content::ofString(<<<KEY
                        -----BEGIN PRIVATE KEY-----
                        MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgSmB1mBZDN7uKBA4p
                        auujhPQ6DSqMCQj5i/8GWKBTSD2gCgYIKoZIzj0DAQehRANCAARy8AXnCbwjw49e
                        8Vmmhn7UxrWDp+EcZfIj2A+CxTbZ+8STxZbe18qeq/YaJeWQgtiIByNmQcRIbwW5
                        CM/EsUjo
                        -----END PRIVATE KEY-----
                        KEY
                    ),
                );

                $sdk = SDK::of(
                    $clock,
                    $transport,
                    $key,
                    Period::minute(1),
                );

                $this->assertInstanceOf(Storefronts::class, $sdk->storefronts());
                $this->assertInstanceOf(Catalog::class, $sdk->catalog($storefront));
                $this->assertNotSame('', $sdk->jwt());
                $sdk->storefronts()->all(); // trigger the assertion on the transport
            });
    }
}
