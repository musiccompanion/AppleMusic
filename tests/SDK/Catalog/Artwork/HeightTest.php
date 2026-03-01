<?php
declare(strict_types = 1);

namespace Tests\MusicCompanion\AppleMusic\SDK\Catalog\Artwork;

use MusicCompanion\AppleMusic\{
    SDK\Catalog\Artwork\Height,
    Exception\DomainException,
};
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class HeightTest extends TestCase
{
    use BlackBox;

    public function testItCanBeOfAnyNaturalNumber(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\NaturalNumbersExceptZero::any())
            ->prove(function(int $number) {
                $height = Height::of($number);

                $this->assertSame($number, $height->toInt());
                $this->assertSame((string) $number, $height->toString());
            });
    }

    public function testNegativeNumbersAreNotAccepted(): BlackBox\Proof
    {
        return $this
            ->forAll(Set\Integers::below(1))
            ->prove(function(int $negative) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage((string) $negative);

                Height::of($negative);
            });
    }
}
