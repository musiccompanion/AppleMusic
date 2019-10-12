<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork\{
    Width,
    Height,
};
use Innmind\Url\UrlInterface;
use Innmind\Colour\RGBA;

final class Artwork
{
    private $width;
    private $height;
    private $url;
    private $backgroundColor;
    private $textColor1;
    private $textColor2;
    private $textColor3;
    private $textColor4;

    public function __construct(
        Width $width,
        Height $height,
        UrlInterface $url,
        RGBA $backgroundColor,
        RGBA $textColor1,
        RGBA $textColor2,
        RGBA $textColor3,
        RGBA $textColor4
    ) {
        $this->width = $width;
        $this->height = $height;
        $this->url = $url;
        $this->backgroundColor = $backgroundColor;
        $this->textColor1 = $textColor1;
        $this->textColor2 = $textColor2;
        $this->textColor3 = $textColor3;
        $this->textColor4 = $textColor4;
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

    public function backgroundColor(): RGBA
    {
        return $this->backgroundColor;
    }

    public function textColor1(): RGBA
    {
        return $this->textColor1;
    }

    public function textColor2(): RGBA
    {
        return $this->textColor2;
    }

    public function textColor3(): RGBA
    {
        return $this->textColor3;
    }

    public function textColor4(): RGBA
    {
        return $this->textColor4;
    }
}
