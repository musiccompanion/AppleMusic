<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK;

use MusicCompanion\AppleMusic\SDK\HttpTransport;
use Innmind\HttpTransport\{
    Transport,
    Success,
    ClientError,
    ServerError,
};
use Innmind\Http\{
    Request,
    Response,
    Method,
    Response\StatusCode,
    ProtocolVersion,
};
use Innmind\Immutable\Either;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Url;

class HttpTransportTest extends TestCase
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
                $fulfill = new HttpTransport(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = Request::of(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = Response::of(
                    StatusCode::of($statusCode),
                    ProtocolVersion::v20,
                );
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->with($this->callback(static function($request) use ($initial): bool {
                        return $request->url()->scheme()->toString() === 'https' &&
                            $request->url()->authority()->toString() === 'api.music.apple.com' &&
                            $request->url()->path() === $initial->url()->path() &&
                            $request->url()->query() === $initial->url()->query() &&
                            $request->method() === $initial->method() &&
                            $request->protocolVersion() === $initial->protocolVersion() &&
                            $request->headers() === $initial->headers() &&
                            $request->body() === $initial->body();
                    }))
                    ->willReturn(Either::right(new Success(
                        $initial,
                        $response,
                    )));

                $this->assertSame($response, $fulfill($initial)->match(
                    static fn($response) => $response,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingOnClientError()
    {
        $this
            ->forAll(
                Url::any(),
                Set\Elements::of(...\range(400, 418)),
            )
            ->then(function($url, $statusCode) {
                $fulfill = new HttpTransport(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = Request::of(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = Response::of(
                    StatusCode::of($statusCode),
                    ProtocolVersion::v20,
                );
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn(Either::left(new ClientError(
                        $initial,
                        $response,
                    )));

                $this->assertNull($fulfill($initial)->match(
                    static fn($response) => $response,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingOnServerError()
    {
        $this
            ->forAll(
                Url::any(),
                Set\Integers::between(500, 508),
            )
            ->then(function($url, $statusCode) {
                $fulfill = new HttpTransport(
                    $inner = $this->createMock(Transport::class),
                );
                $initial = Request::of(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = Response::of(
                    StatusCode::of($statusCode),
                    ProtocolVersion::v20,
                );
                $inner
                    ->expects($this->once())
                    ->method('__invoke')
                    ->willReturn(Either::left(new ServerError($initial, $response)));

                $this->assertNull($fulfill($initial)->match(
                    static fn($response) => $response,
                    static fn() => null,
                ));
            });
    }
}
