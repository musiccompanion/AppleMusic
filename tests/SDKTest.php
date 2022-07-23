<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\{
    SDK,
    SDK\Storefronts,
    SDK\Catalog,
    SDK\Library,
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
use Innmind\Http\Message\{
    Request,
    Response,
    StatusCode,
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
                new Set\Strings,
            )
            ->then(function($storefront, $userToken) {
                $clock = $this->createMock(Clock::class);
                $transport = $this->createMock(Transport::class);
                $key = new Key(
                    'AAAAAAAAAA',
                    'BBBBBBBBBB',
                    // this is a randomly generated key
                    Content\Lines::ofContent(<<<KEY
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
                $response = $this->createMock(Response::class);
                $response
                    ->method('statusCode')
                    ->willReturn(StatusCode::ok);
                $response
                    ->method('body')
                    ->willReturn($body = $this->createMock(Content::class));
                $body
                    ->method('toString')
                    ->willReturn('{"data":[]}');
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
                    ->willReturn(Either::right(new Success(
                        $this->createMock(Request::class),
                        $response,
                    )));

                $sdk = new SDK(
                    $clock,
                    $transport,
                    $key,
                    new Minute(1),
                );

                $this->assertInstanceOf(Storefronts::class, $sdk->storefronts());
                $this->assertInstanceOf(Catalog::class, $sdk->catalog($storefront));
                $this->assertInstanceOf(Library::class, $sdk->library($userToken));
                $this->assertNotEmpty($sdk->jwt());
                $sdk->storefronts()->all(); // trigger the assertion on the transport
            });
    }
}
