<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\{
    SDK\SDK,
    SDK as SDKInterface,
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
use Innmind\HttpTransport\Transport;
use Innmind\Http\Message\{
    Response,
    StatusCode,
};
use Innmind\Stream\Readable;
use function Innmind\Immutable\first;
use Lcobucci\JWT\Parser;
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
                new Set\Strings
            )
            ->then(function($storefront, $userToken) {
                $clock = $this->createMock(Clock::class);
                $transport = $this->createMock(Transport::class);
                $key = new Key(
                    'AAAAAAAAAA',
                    'BBBBBBBBBB',
                    $content = $this->createMock(Readable::class)
                );
                // this is a randomly generated key
                $content
                    ->method('toString')
                    ->willReturn(<<<KEY
-----BEGIN PRIVATE KEY-----
MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQgSmB1mBZDN7uKBA4p
auujhPQ6DSqMCQj5i/8GWKBTSD2gCgYIKoZIzj0DAQehRANCAARy8AXnCbwjw49e
8Vmmhn7UxrWDp+EcZfIj2A+CxTbZ+8STxZbe18qeq/YaJeWQgtiIByNmQcRIbwW5
CM/EsUjo
-----END PRIVATE KEY-----
KEY
                    );
                $clock
                    ->method('now')
                    ->willReturn(new PointInTime('2019-01-01T00:00:00'));
                $transport
                    ->expects($this->any())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) {
                        $header = first($request->headers()->get('authorization')->values())->toString();
                        $jwt = \substr($header, 7); // remove Bearer
                        $jwt = (new Parser)->parse($jwt);

                        return $jwt->getHeader('kid') === 'AAAAAAAAAA' &&
                            $jwt->getClaim('iss') === 'BBBBBBBBBB' &&
                            $jwt->getClaim('iat') === 1546300800 &&
                            $jwt->getClaim('exp') === 1546300860;
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->method('statusCode')
                    ->willReturn(new StatusCode(200));
                $response
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->method('toString')
                    ->willReturn('{"data":[]}');

                $sdk = new SDK(
                    $clock,
                    $transport,
                    $key,
                    new Minute(1)
                );

                $this->assertInstanceOf(SDKInterface::class, $sdk);
                $this->assertInstanceOf(Storefronts::class, $sdk->storefronts());
                $this->assertInstanceOf(Catalog::class, $sdk->catalog($storefront));
                $this->assertInstanceOf(Library::class, $sdk->library($userToken));
                $this->assertNotEmpty($sdk->jwt());
                $sdk->storefronts()->all(); // trigger the assertion on the transport
            });
    }
}
