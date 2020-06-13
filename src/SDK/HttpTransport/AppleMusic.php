<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\HttpTransport;

use MusicCompanion\AppleMusic\Exception\{
    InvalidToken,
    InvalidUserToken,
};
use Innmind\HttpTransport\{
    Transport,
    ThrowOnErrorTransport,
    Exception\ClientError,
};
use Innmind\Http\Message\{
    Request,
    Response,
    StatusCode,
};
use Innmind\Url\Url;

final class AppleMusic implements Transport
{
    private Transport $fulfill;
    private Url $url;

    public function __construct(Transport $fulfill)
    {
        $this->fulfill = new ThrowOnErrorTransport($fulfill);
        $this->url = Url::of('https://api.music.apple.com/');
    }

    public function __invoke(Request $request): Response
    {
        try {
            return ($this->fulfill)(new Request\Request(
                $this
                    ->url
                    ->withPath($request->url()->path())
                    ->withQuery($request->url()->query()),
                $request->method(),
                $request->protocolVersion(),
                $request->headers(),
                $request->body(),
            ));
        } catch (ClientError $e) {
            if ($e->response()->statusCode()->equals(StatusCode::of('UNAUTHORIZED'))) {
                throw new InvalidToken('', 0, $e);
            }

            if ($e->response()->statusCode()->equals(StatusCode::of('FORBIDDEN'))) {
                throw new InvalidUserToken('', 0, $e);
            }

            throw $e;
        }
    }
}
