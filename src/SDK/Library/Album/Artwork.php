<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\{
    Width,
    Height,
};
use Innmind\Url\{
    Url,
    Path,
};
use Innmind\Immutable\{
    Str,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Artwork
{
    /** @var Maybe<Width> */
    private Maybe $width;
    /** @var Maybe<Height> */
    private Maybe $height;
    private Url $url;

    /**
     * @param Maybe<Width> $width
     * @param Maybe<Height> $height
     */
    public function __construct(
        Maybe $width,
        Maybe $height,
        Url $url,
    ) {
        $this->width = $width;
        $this->height = $height;
        $this->url = $url;
    }

    /**
     * @return Maybe<Width>
     */
    public function width(): Maybe
    {
        return $this->width;
    }

    /**
     * @return Maybe<Height>
     */
    public function height(): Maybe
    {
        return $this->height;
    }

    public function url(): Url
    {
        return $this->url;
    }

    public function ofSize(Width $width, Height $height): Url
    {
        $path = Str::of($this->url->path()->toString())
            ->replace('{w}', $width->toString())
            ->replace('{h}', $height->toString())
            ->toString();

        return $this->url->withPath(Path::of($path));
    }
}
