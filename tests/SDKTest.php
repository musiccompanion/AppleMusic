<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\{
    SDK,
    SDK\Storefronts,
    SDK\Catalog,
    Key,
};
use Innmind\TimeContinuum\{
    Clock,
    Earth\PointInTime\PointInTime,
    Earth\Period\Minute,
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
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class SDKTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(
                Storefront\Id::any(),
                Set\Strings::any(),
            )
            ->then(function($storefront, $userToken) {
                $clock = $this->createMock(Clock::class);
                $transport = $this->createMock(Transport::class);
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
                $clock
                    ->method('now')
                    ->willReturn(new PointInTime('2019-01-01T00:00:00+00:00'));
                $response = Response::of(
                    StatusCode::ok,
                    ProtocolVersion::v11,
                    null,
                    Content::ofString('{"data":[]}'),
                );
                $transport
                    ->expects($this->any())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) {
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

                        return $jwt->headers()->get('kid') === 'AAAAAAAAAA' &&
                            $jwt->claims()->get('iss') === 'BBBBBBBBBB' &&
                            $jwt->claims()->get('iat')->format(\DateTime::ATOM) === '2019-01-01T00:00:00+00:00' &&
                            $jwt->claims()->get('exp')->format(\DateTime::ATOM) === '2019-01-01T00:01:00+00:00';
                    }))
                    ->willReturnCallback(static fn($request) => Either::right(new Success(
                        $request,
                        $response,
                    )));

                $sdk = SDK::of(
                    $clock,
                    $transport,
                    $key,
                    new Minute(1),
                );

                $this->assertInstanceOf(Storefronts::class, $sdk->storefronts());
                $this->assertInstanceOf(Catalog::class, $sdk->catalog($storefront));
                $this->assertNotEmpty($sdk->jwt());
                $sdk->storefronts()->all(); // trigger the assertion on the transport
            });
    }
}
