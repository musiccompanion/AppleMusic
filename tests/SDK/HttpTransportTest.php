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
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};
use Fixtures\Innmind\Url\Url;

class HttpTransportTest extends TestCase
{
    use BlackBox;

    public function testTheUrlIsRewrittenToPointToTheAPIDomain(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Url::any(),
                Set\Integers::between(200, 208),
            )
            ->prove(function($url, $statusCode) {
                $initial = Request::of(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );
                $response = Response::of(
                    StatusCode::of($statusCode),
                    ProtocolVersion::v20,
                );
                $fulfill = new HttpTransport(
                    Transport::via(function($request) use ($initial, $response) {
                        $this->assertSame(
                            'https',
                            $request->url()->scheme()->toString(),
                        );
                        $this->assertSame(
                            'api.music.apple.com',
                            $request->url()->authority()->toString(),
                        );
                        $this->assertSame(
                            $initial->url()->path(),
                            $request->url()->path(),
                        );
                        $this->assertSame(
                            $initial->url()->query(),
                            $request->url()->query(),
                        );
                        $this->assertSame(
                            $initial->method(),
                            $request->method(),
                        );
                        $this->assertSame(
                            $initial->protocolVersion(),
                            $request->protocolVersion(),
                        );
                        $this->assertSame(
                            $initial->headers(),
                            $request->headers(),
                        );
                        $this->assertSame(
                            $initial->body(),
                            $request->body(),
                        );

                        return Either::right(new Success(
                            $initial,
                            $response,
                        ));
                    }),
                );

                $this->assertSame($response, $fulfill($initial)->match(
                    static fn($response) => $response,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingOnClientError(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Url::any(),
                Set\Elements::of(...\range(400, 418)),
            )
            ->prove(function($url, $statusCode) {
                $fulfill = new HttpTransport(
                    Transport::via(static fn($request) => Either::left(new ClientError(
                        $request,
                        Response::of(
                            StatusCode::of($statusCode),
                            ProtocolVersion::v20,
                        ),
                    ))),
                );
                $initial = Request::of(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );

                $this->assertNull($fulfill($initial)->match(
                    static fn($response) => $response,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingOnServerError(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Url::any(),
                Set\Integers::between(500, 508),
            )
            ->prove(function($url, $statusCode) {
                $fulfill = new HttpTransport(
                    Transport::via(static fn($request) => Either::left(new ServerError(
                        $request,
                        Response::of(
                            StatusCode::of($statusCode),
                            ProtocolVersion::v20,
                        ),
                    ))),
                );
                $initial = Request::of(
                    $url,
                    Method::get,
                    ProtocolVersion::v20,
                );

                $this->assertNull($fulfill($initial)->match(
                    static fn($response) => $response,
                    static fn() => null,
                ));
            });
    }
}
