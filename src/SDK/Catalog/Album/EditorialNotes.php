<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Catalog\Album;

/**
 * @psalm-immutable
 */
final class EditorialNotes
{
    private string $standard;
    private string $short;

    private function __construct(string $standard, string $short)
    {
        $this->standard = $standard;
        $this->short = $short;
    }

    /**
     * @psalm-pure
     */
    public static function of(string $standard, string $short): self
    {
        return new self($standard, $short);
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
