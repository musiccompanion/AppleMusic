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
use Innmind\Immutable\Str;

final class Artwork
{
    private Width $width;
    private Height $height;
    private Url $url;

    public function __construct(
        Width $width,
        Height $height,
        Url $url
    ) {
        $this->width = $width;
        $this->height = $height;
        $this->url = $url;
    }

    public function width(): Width
    {
        return $this->width;
    }

    public function height(): Height
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
