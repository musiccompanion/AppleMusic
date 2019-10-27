<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\HttpTransport;

use Innmind\HttpTransport\{
    Transport,
    ThrowOnErrorTransport,
};
use Innmind\Http\Message\{
    Request,
    Response,
};
use Innmind\Url\Url;

final class AppleMusic implements Transport
{
    private $fulfill;
    private $url;

    public function __construct(Transport $fulfill)
    {
        $this->fulfill = new ThrowOnErrorTransport($fulfill);
        $this->url = Url::fromString('https://api.music.apple.com/');
    }

    public function __invoke(Request $request): Response
    {
        return ($this->fulfill)(new Request\Request(
            $this
                ->url
                ->withPath($request->url()->path())
                ->withQuery($request->url()->query()),
            $request->method(),
            $request->protocolVersion(),
            $request->headers(),
            $request->body()
        ));
    }
}
