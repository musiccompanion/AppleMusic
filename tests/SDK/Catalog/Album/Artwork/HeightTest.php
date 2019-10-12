<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Album\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Album\Artwork\Height,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class HeightTest extends TestCase
{
    use BlackBox;

    public function testItCanBeOfAnyNaturalNumber()
    {
        $this
            ->forAll(new Set\NaturalNumbersExceptZero)
            ->then(function(int $number) {
                $height = new Height($number);

                $this->assertSame($number, $height->toInt());
                $this->assertSame((string) $number, (string) $height);
            });
    }

    public function testNegativeNumbersAreNotAccepted()
    {
        $this
            ->forAll(Set\Integers::of(null, 1))
            ->then(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                new Height($negative);
            });
    }
}
