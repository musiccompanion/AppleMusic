<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK;

use Innmind\HttpTransport\Transport;
use Innmind\Http\Message\{
    Request,
    Response,
};
use Innmind\Url\Url;
use Innmind\Immutable\Maybe;

final class HttpTransport
{
    private Transport $fulfill;
    private Url $url;

    public function __construct(Transport $fulfill)
    {
        $this->fulfill = $fulfill;
        $this->url = Url::of('https://api.music.apple.com/');
    }

    /**
     * @return Maybe<Response>
     */
    public function __invoke(Request $request): Maybe
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
            ->maybe()
            ->map(static fn($success) => $success->response());
    }
}
