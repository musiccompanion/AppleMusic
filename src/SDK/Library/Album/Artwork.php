<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Library\Album;

use MusicCompanion\AppleMusic\SDK\Library\Album\Artwork\{
    Width,
    Height,
};
use Innmind\Url\UrlInterface;

final class Artwork
{
    private $width;
    private $height;
    private $url;

    public function __construct(
        Width $width,
        Height $height,
        UrlInterface $url
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

    public function url(): UrlInterface
    {
        return $this->url;
    }
}
