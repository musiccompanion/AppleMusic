<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Album;

final class EditorialNotes
{
    private $standard;
    private $short;

    public function __construct(string $standard, string $short)
    {
        $this->standard = $standard;
        $this->short = $short;
    }

    public function standard(): string
    {
        return $this->standard;
    }

    public function short(): string
    {
        return $this->short;
    }
}
