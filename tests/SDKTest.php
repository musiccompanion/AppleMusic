<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic;

use MusicCompanion\AppleMusic\{
    SDK,
    Key,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    PointInTime\Earth\PointInTime,
    Period\Earth\Minute,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\Message\Response;
use Innmind\Stream\Readable;
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
            ->take(100)
            ->then(function($storefront, $userToken) {
                $clock = $this->createMock(TimeContinuumInterface::class);
                $transport = $this->createMock(Transport::class);
                $key = new Key(
                    'AAAAAAAAAA',
                    'BBBBBBBBBB',
                    $content = $this->createMock(Readable::class)
                );
                // this is a randomly generated key
                $content
                    ->method('__toString')
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
                        $jwt = $request->headers()->get('authorization')->values()->current()->parameter();
                        $jwt = (new Parser)->parse($jwt);

                        return $jwt->getHeader('kid') === 'AAAAAAAAAA' &&
                            $jwt->getClaim('iss') === 'BBBBBBBBBB' &&
                            $jwt->getClaim('iat') === 1546300800 &&
                            $jwt->getClaim('exp') === 1546300860;
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->method('body')
                    ->willReturn($body = $this->createMock(Readable::class));
                $body
                    ->method('__toString')
                    ->willReturn('{"data":[]}');

                $sdk = new SDK(
                    $clock,
                    $transport,
                    $key,
                    new Minute(1)
                );

                $this->assertInstanceOf(SDK\Storefronts::class, $sdk->storefronts());
                $this->assertInstanceOf(SDK\Catalog::class, $sdk->catalog($storefront));
                $this->assertInstanceOf(SDK\Library::class, $sdk->library($userToken));
                $sdk->storefronts()->all(); // trigger the assertion on the transport
            });
    }
}