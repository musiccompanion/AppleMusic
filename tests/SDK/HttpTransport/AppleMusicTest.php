<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\HttpTransport;

use MusicCompanion\AppleMusic\SDK\HttpTransport\AppleMusic;
use Innmind\HttpTransport\{
    Transport,
    Exception\ClientError,
    Exception\ServerError,
};
use Innmind\Http\{
    Message\Request\Request,
    Message\Response,
    Message\Method\Method,
    Message\StatusCode\StatusCode,
    ProtocolVersion\ProtocolVersion,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class AppleMusicTest extends TestCase
{
    use BlackBox;

    public function testTheUrlIsRewrittenToPointToTheAPIDomain()
    {
        $this
            ->forAll(
                Set\Url::of(),
                Set\Integers::of(200, 208)
            )
            ->take(100)
            ->then(function($url, $statusCode) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class)
                );
                $initial = new Request(
                    $url,
                    Method::get(),
                    new ProtocolVersion(2, 0)
                );
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($url, $initial): bool {
                        return (string) $request->url()->scheme() === 'https' &&
                            (string) $request->url()->authority() === 'api.music.apple.com' &&
                            $request->url()->path() === $initial->url()->path() &&
                            $request->url()->query() === $initial->url()->query() &&
                            $request->method() === $initial->method() &&
                            $request->protocolVersion() === $initial->protocolVersion() &&
                            $request->headers() === $initial->headers() &&
                            $request->body() === $initial->body();
                    }))
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(new StatusCode($statusCode));

                $this->assertSame($response, $fulfill($initial));
            });
    }

    public function testThrowOnClientError()
    {
        $this
            ->forAll(
                Set\Url::of(),
                Set\Integers::of(400, 418)
            )
            ->take(100)
            ->then(function($url, $statusCode) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class)
                );
                $initial = new Request(
                    $url,
                    Method::get(),
                    new ProtocolVersion(2, 0)
                );
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(new StatusCode($statusCode));

                $this->expectException(ClientError::class);

                $fulfill($initial);
            });
    }

    public function testThrowOnServerError()
    {
        $this
            ->forAll(
                Set\Url::of(),
                Set\Integers::of(500, 508)
            )
            ->take(100)
            ->then(function($url, $statusCode) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class)
                );
                $initial = new Request(
                    $url,
                    Method::get(),
                    new ProtocolVersion(2, 0)
                );
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn($response = $this->createMock(Response::class));
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(new StatusCode($statusCode));

                $this->expectException(ServerError::class);

                $fulfill($initial);
            });
    }
}
