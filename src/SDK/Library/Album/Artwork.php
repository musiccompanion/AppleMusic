<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\{
    Width,
    Height,
};
use Innmind\Url\Url;

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
}
