<?php
declare(strict_types = 1);

namespace MusicCompanion\AppleMusic\SDK\Storefront;

final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
