<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\HttpTransport;

use MusicCompanion\AppleMusic\{
    SDK\HttpTransport\AppleMusic,
    Exception\InvalidToken,
    Exception\InvalidUserToken,
};
use Innmind\HttpTransport\{
    Transport,
    Success,
    ClientError,
    ServerError,
};
use Innmind\Http\{
    Message\Request\Request,
    Message\Response,
    Message\Method,
    Message\StatusCode,
    ProtocolVersion,
};
use Innmind\Immutable\Either;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Url;

class AppleMusicTest extends TestCase
{
    use BlackBox;

    public function testTheUrlIsRewrittenToPointToTheAPIDomain()
    {
        $this
            ->forAll(
                Url::any(),
                Set\Integers::between(200, 208),
            )
            ->then(function($url, $statusCode) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = new Request(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = $this->createMock(Response::class);
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(StatusCode::of($statusCode));
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($url, $initial): bool {
                        return $request->url()->scheme()->toString() === 'https' &&
                            $request->url()->authority()->toString() === 'api.music.apple.com' &&
                            $request->url()->path() === $initial->url()->path() &&
                            $request->url()->query() === $initial->url()->query() &&
                            $request->method() === $initial->method() &&
                            $request->protocolVersion() === $initial->protocolVersion() &&
                            $request->headers() === $initial->headers() &&
                            $request->body() === $initial->body();
                    }))
                    ->willReturn($expected = Either::right(new Success(
                        $initial,
                        $response,
                    )));

                $this->assertEquals($expected, $fulfill($initial));
            });
    }

    public function testThrowOnClientError()
    {
        $this
            ->forAll(
                Url::any(),
                Set\Elements::of(400, 402, ...\range(404, 418)),
            )
            ->then(function($url, $statusCode) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = new Request(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = $this->createMock(Response::class);
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(StatusCode::of($statusCode));
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn(Either::left(new ClientError(
                        $initial,
                        $response,
                    )));

                $this->expectException(\RuntimeException::class);

                $fulfill($initial);
            });
    }

    public function testThrowWhenInvalidToken()
    {
        $this
            ->forAll(Url::any())
            ->then(function($url) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = new Request(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = $this->createMock(Response::class);
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(StatusCode::unauthorized);
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn(Either::left(new ClientError($initial, $response)));

                $this->expectException(InvalidToken::class);

                $fulfill($initial);
            });
    }

    public function testThrowWhenInvalidUserToken()
    {
        $this
            ->forAll(Url::any())
            ->then(function($url) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = new Request(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = $this->createMock(Response::class);
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(StatusCode::forbidden);
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn(Either::left(new ClientError($initial, $response)));

                $this->expectException(InvalidUserToken::class);

                $fulfill($initial);
            });
    }

    public function testThrowOnServerError()
    {
        $this
            ->forAll(
                Url::any(),
                Set\Integers::between(500, 508),
            )
            ->then(function($url, $statusCode) {
                $fulfill = new AppleMusic(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = new Request(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = $this->createMock(Response::class);
                $response
                    ->expects($this->any())
                    ->method('statusCode')
                    ->willReturn(StatusCode::of($statusCode));
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn(Either::left(new ServerError($initial, $response)));

                $this->expectException(\RuntimeException::class);

                $fulfill($initial);
            });
    }
}
