<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Storefront;

/**
 * @psalm-immutable
 */
final class Name
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @psalm-pure
     */
    public static function of(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
