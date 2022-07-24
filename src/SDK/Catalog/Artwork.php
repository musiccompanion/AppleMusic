<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog;

use MusicCompanion\AppleMusic\SDK\Catalog\Artwork\{
    Width,
    Height,
};
use Innmind\Url\{
    Url,
    Path,
};
use Innmind\Colour\RGBA;
use Innmind\Immutable\{
    Str,
    Maybe,
};

final class Artwork
{
    private Width $width;
    private Height $height;
    private Url $url;
    /** @var Maybe<RGBA> */
    private Maybe $backgroundColor;
    /** @var Maybe<RGBA> */
    private Maybe $textColor1;
    /** @var Maybe<RGBA> */
    private Maybe $textColor2;
    /** @var Maybe<RGBA> */
    private Maybe $textColor3;
    /** @var Maybe<RGBA> */
    private Maybe $textColor4;

    /**
     * @param Maybe<RGBA> $backgroundColor
     * @param Maybe<RGBA> $textColor1
     * @param Maybe<RGBA> $textColor2
     * @param Maybe<RGBA> $textColor3
     * @param Maybe<RGBA> $textColor4
     */
    public function __construct(
        Width $width,
        Height $height,
        Url $url,
        Maybe $backgroundColor,
        Maybe $textColor1,
        Maybe $textColor2,
        Maybe $textColor3,
        Maybe $textColor4,
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

    public function url(): Url
    {
        return $this->url;
    }

    /**
     * @return Maybe<RGBA>
     */
    public function backgroundColor(): Maybe
    {
        return $this->backgroundColor;
    }

    /**
     * @return Maybe<RGBA>
     */
    public function textColor1(): Maybe
    {
        return $this->textColor1;
    }

    /**
     * @return Maybe<RGBA>
     */
    public function textColor2(): Maybe
    {
        return $this->textColor2;
    }

    /**
     * @return Maybe<RGBA>
     */
    public function textColor3(): Maybe
    {
        return $this->textColor3;
    }

    /**
     * @return Maybe<RGBA>
     */
    public function textColor4(): Maybe
    {
        return $this->textColor4;
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
