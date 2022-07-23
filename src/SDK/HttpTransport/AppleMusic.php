<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\HttpTransport;

use MusicCompanion\AppleMusic\Exception\{
    InvalidToken,
    InvalidUserToken,
};
use Innmind\HttpTransport\{
    Transport,
    ClientError,
};
use Innmind\Http\Message\{
    Request,
    StatusCode,
};
use Innmind\Url\Url;
use Innmind\Immutable\Either;

final class AppleMusic implements Transport
{
    private Transport $fulfill;
    private Url $url;

    public function __construct(Transport $fulfill)
    {
        $this->fulfill = $fulfill;
        $this->url = Url::of('https://api.music.apple.com/');
    }

    public function __invoke(Request $request): Either
    {
        return ($this->fulfill)(new Request\Request(
            $this
                ->url
                ->withPath($request->url()->path())
                ->withQuery($request->url()->query()),
            $request->method(),
            $request->protocolVersion(),
            $request->headers(),
            $request->body(),
        ))
            ->leftMap(static fn($left) => match (true) {
                $left instanceof ClientError => match ($left->response()->statusCode()) {
                    StatusCode::unauthorized => throw new InvalidToken,
                    StatusCode::forbidden => throw new InvalidUserToken,
                    default => throw new \RuntimeException,
                },
                default => throw new \RuntimeException,
            });
    }
}
